<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Service\HttpPush;

use Psr\Http\Message\ServerRequestInterface;
use SFC\Staticfilecache\Service\ConfigurationService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ScriptHttpPush extends AbstractHttpPush
{
    public function __construct(protected readonly ConfigurationService $configurationService) {}

    /**
     * Check if the class can handle the file extension.
     */
    public function canHandleExtension(string $fileExtension): bool
    {
        return 'js' === $fileExtension;
    }

    /**
     * Get headers for the current file extension.
     *
     * @return list<array{path: string, type: string}>
     */
    public function getHeaders(string $content, ServerRequestInterface $request): array
    {
        $content = $this->removeSkippedScriptTags($content);

        if (!preg_match_all('/(?<=src=")(?<src>[^"]+?\.js(\.gzi?p?)?(\?\d+)?)(?=")/', $content, $jsFiles)) {
            return [];
        }

        $paths = $this->streamlineFilePaths($jsFiles['src'], $request);

        return $this->mapPathsWithType($paths, 'script');
    }

    /**
     * Remove the opening script tags that carry one of the configured attributes
     * (e.g. async or defer), so their sources are not pushed.
     *
     * @see https://github.com/lochmueller/staticfilecache/issues/433
     */
    protected function removeSkippedScriptTags(string $content): string
    {
        $patterns = array_map(
            $this->getAttributePattern(...),
            GeneralUtility::trimExplode(',', (string) $this->configurationService->get('sendHttp2PushSkipScriptAttributes'), true)
        );
        if ([] === $patterns) {
            return $content;
        }

        return (string) preg_replace_callback('/<script\b[^>]*>/i', static function (array $match) use ($patterns): string {
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $match[0])) {
                    return '';
                }
            }

            return $match[0];
        }, $content);
    }

    /**
     * "name" matches the attribute with any (or without) value, "name=value" only the given value.
     */
    protected function getAttributePattern(string $attribute): string
    {
        [$name, $value] = array_pad(explode('=', $attribute, 2), 2, null);
        $name = preg_quote(trim($name), '/');

        if (null === $value) {
            return '/\s' . $name . '(?=[\s=\/>])/i';
        }

        $value = preg_quote(trim($value, " \t\"'"), '/');

        return '/\s' . $name . '\s*=\s*(["\']?)' . $value . '\1(?=[\s\/>])/i';
    }
}

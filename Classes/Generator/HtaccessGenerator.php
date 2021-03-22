<?php

/**
 * HtaccessGenerator.
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Generator;

use Psr\Http\Message\ResponseInterface;
use SFC\Staticfilecache\Service\ConfigurationService;
use SFC\Staticfilecache\Service\DateTimeService;
use SFC\Staticfilecache\Service\RemoveService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * HtaccessGenerator.
 */
class HtaccessGenerator extends AbstractGenerator
{
    /**
     * Generate file.
     */
    public function generate(string $entryIdentifier, string $fileName, ResponseInterface $response, int $lifetime): void
    {
        $configuration = GeneralUtility::makeInstance(ConfigurationService::class);

        $htaccessFile = PathUtility::pathinfo($fileName, PATHINFO_DIRNAME).'/.htaccess';
        $accessTimeout = (int) $configuration->get('htaccessTimeout');
        $lifetime = $accessTimeout ?: $lifetime;

        $headers = $this->getReponseHeaders($response);
        if ($configuration->isBool('debugHeaders')) {
            $headers['X-SFC-State'] = 'StaticFileCache - via htaccess';
        }

        $contentType = 'text/html';
        if (isset($headers['Content-Type']) && preg_match('/[a-z-]*\/[a-z-]*/', $headers['Content-Type'], $matches)) {
            $contentType = $matches[0];
        }

        $headers = array_map(fn ($item) => str_replace('"', '\"', $item), $headers);

        $sendCacheControlHeader = isset($GLOBALS['TSFE']->config['config']['sendCacheHeaders']) ? (bool) $GLOBALS['TSFE']->config['config']['sendCacheHeaders'] : false;

        $variables = [
            'contentType' => $contentType,
            'mode' => $accessTimeout ? 'A' : 'M',
            'lifetime' => $lifetime,
            'TIME' => '{TIME}',
            'expires' => (new DateTimeService())->getCurrentTime() + $lifetime,
            'sendCacheControlHeader' => $sendCacheControlHeader,
            'sendCacheControlHeaderRedirectAfterCacheTimeout' => $configuration->isBool('sendCacheControlHeaderRedirectAfterCacheTimeout'),
            'headers' => $headers,
        ];

        $this->renderTemplateToFile($this->getTemplateName(), $variables, $htaccessFile);
    }

    /**
     * Remove file.
     */
    public function remove(string $entryIdentifier, string $fileName): void
    {
        $htaccessFile = PathUtility::pathinfo($fileName, PATHINFO_DIRNAME).'/.htaccess';
        $removeService = GeneralUtility::makeInstance(RemoveService::class);
        $removeService->file($htaccessFile);
    }

    /**
     * Get reponse headers.
     */
    protected function getReponseHeaders(ResponseInterface $response): array
    {
        $configuration = GeneralUtility::makeInstance(ConfigurationService::class);
        $validHeaders = GeneralUtility::trimExplode(',', $configuration->get('validHtaccessHeaders').',Content-Type', true);

        $headers = $response->getHeaders();
        $result = [];
        foreach ($headers as $name => $values) {
            if (\in_array($name, $validHeaders, true)) {
                $result[$name] = implode(', ', $values);
            }
        }

        return $result;
    }

    /**
     * Render template to file.
     */
    protected function renderTemplateToFile(string $templateName, array $variables, string $htaccessFile): void
    {
        /** @var StandaloneView $renderer */
        $renderer = GeneralUtility::makeInstance(StandaloneView::class);
        $renderer->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName($templateName));
        $renderer->assignMultiple($variables);
        $content = trim((string) $renderer->render());
        // Note: Create even empty htaccess files (do not check!!!), so the delete is in sync
        GeneralUtility::writeFile($htaccessFile, $content);
    }

    /**
     * Get the template name.
     */
    protected function getTemplateName(): string
    {
        $configuration = GeneralUtility::makeInstance(ConfigurationService::class);
        $templateName = trim((string) $configuration->get('htaccessTemplateName'));
        if ('' === $templateName) {
            return 'EXT:staticfilecache/Resources/Private/Templates/Htaccess.html';
        }

        return $templateName;
    }
}

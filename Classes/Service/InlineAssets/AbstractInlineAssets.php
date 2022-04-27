<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Service\InlineAssets;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class AbstractInlineFiles.
 *
 * @author Marcus Förster ; https://github.com/xerc
 */
abstract class AbstractInlineAssets extends \SFC\Staticfilecache\Service\HttpPush\AbstractHttpPush
{
    public function __construct()
    {
        $this->sitePath = \TYPO3\CMS\Core\Core\Environment::getPublicPath(); // [^/]$

        // @var ConfigurationService $configurationService
        $this->configurationService = GeneralUtility::makeInstance(\SFC\Staticfilecache\Service\ConfigurationService::class); // CHECK ; src-location?!
    }

    abstract public function replaceInline(string $content): string;

    public function getHeaders(string $content): array
    {
        return []; // HACK
    }

    protected function includeAssets(string $regex, string $content): string
    {
        return preg_replace_callback($regex, fn (array $match): string => $this->parseAsset($match), $content);
    }

    protected function parseAsset(array $match): string
    {
        $path = $this->sitePath.$this->streamlineFilePaths((array) $match['src'])[0];
        if (!file_exists($path)) {
            return $match[0];
        }

        if (filesize($path) > $this->configurationService->get('inlineFileSize')) {
            return $match[0];
        }

        $file = file_get_contents($path);
        if (empty($file)) {
            return $match[0];
        }

        switch ($match['ext']) {
          case 'svg':// TODO ; https://github.com/peteboere/css-crush/commit/7cd5d73f67212dfc7ec0f85e4a84932a32ce95d8
              $type = 'image/svg+xml;utf8';
              $file = str_replace('\'', '"', $file); // quotes

              $file = preg_replace('/\s+/', ' ', $file); // whitespace
              $file = preg_replace('/#([a-f0-9]{3,6})/', '%23$1', $file); // MOD:color

          break;

          case 'ico':
              $type = 'image/x-icon;base64';
              $file = base64_encode($file);

          break;

          case 'woff':
          case 'woff2':
              $type = 'font/'.$match['ext'].';base64';
              $file = base64_encode($file);

          break;

          default:
              $type = 'image/'.$match['ext'].';base64';
              $file = base64_encode($file);

          break;
        }

        return "data:{$type},{$file}";
    }
}

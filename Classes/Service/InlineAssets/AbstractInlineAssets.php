<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Service\InlineAssets;

use SFC\Staticfilecache\Service\AbstractService;
use TYPO3\CMS\Core\Core\Environment;
use SFC\Staticfilecache\Service\ConfigurationService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class AbstractInlineFiles.
 *
 * @author Marcus Förster ; https://github.com/xerc
 */
abstract class AbstractInlineAssets extends AbstractService
{
    protected string $sitePath;
    protected ConfigurationService $configurationService;

    public function __construct()
    {
        $this->sitePath = Environment::getPublicPath(); // [^/]$

        // @var ConfigurationService $configurationService
        $this->configurationService = GeneralUtility::makeInstance(ConfigurationService::class); // CHECK ; src-location?!
    }

    /**
     * Check if the class can handle the file extension.
     */
    abstract public function canHandleExtension(string $fileExtension): bool;

    /**
     * Replace all matching Files within given HTML.
     */
    abstract public function replaceInline(string $content): string;

    protected function includeAssets(string $regex, string $content): string
    {
        return preg_replace_callback($regex, fn(array $match): string => $this->parseAsset($match), $content);
    }

    protected function parseAsset(array $match): string
    {
        $path = $this->sitePath . $match['src'];
        if (!file_exists($path)) {
            return $match[0];
        }

        if (filesize($path) > $this->configurationService->get('inlineAssetsFileSize')) {
            return $match[0];
        }

        $file = file_get_contents($path);
        if (empty($file)) {
            return $match[0];
        }

        switch ($match['ext']) {
            case 'svg':// TODO ; https://github.com/peteboere/css-crush/commit/7cd5d73f67212dfc7ec0f85e4a84932a32ce95d8
                $type = 'image/svg+xml;utf8';
                $file = str_replace('"', '\'', $file); // change quotes
                $file = preg_replace('/#(?=[a-f0-9]{3,6})/', '%23', $file); // adapt hex-color
                $file = preg_replace('/(?<=>)\s+|\s+(?=<)/', '', $file); // remove overhead
                $file = preg_replace('/\s+/', ' ', $file); // shrink whitespace

                break;

            case 'ico':
                $type = 'image/x-icon;base64';
                $file = base64_encode($file);

                break;

            case 'woff':
            case 'woff2':
                $type = 'font/' . $match['ext'] . ';base64';
                $file = base64_encode($file);

                break;

            default:
                $type = 'image/' . $match['ext'] . ';base64';
                $file = base64_encode($file);

                break;
        }

        return "data:{$type},{$file}";
    }
}

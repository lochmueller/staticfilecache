<?php

/**
 * IdentifierBuilder.
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Cache;

use SFC\Staticfilecache\Exception;
use SFC\Staticfilecache\Service\CacheService;
use SFC\Staticfilecache\Service\ConfigurationService;
use SFC\Staticfilecache\StaticFileCacheObject;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * IdentifierBuilder.
 */
class IdentifierBuilder extends StaticFileCacheObject
{
    /**
     * Get the cache name for the given URI.
     *
     * @throws \Exception
     */
    public function getFilepath(string $requestUri): string
    {
        if (!$this->isValidEntryIdentifier($requestUri)) {
            throw new \Exception('Invalid RequestUri as cache identifier: '.$requestUri, 2346782);
        }
        $urlParts = parse_url($requestUri);
        $pageIdentifier = [
            'scheme' => $urlParts['scheme'] ?? 'https',
            'host' => $urlParts['host'] ?? 'invalid',
            'port' => $urlParts['port'] ?? ('https' === $urlParts['scheme'] ? 443 : 80),
        ];
        $parts = [
            'pageIdent' => implode('_', $pageIdentifier),
            'path' => trim($urlParts['path'] ?? '', '/'),
            'index' => 'index',
        ];

        if (GeneralUtility::makeInstance(ConfigurationService::class)->isBool('rawurldecodeCacheFileName')) {
            $parts['path'] = rawurldecode($parts['path']);
        }

        $absoluteBasePath = GeneralUtility::makeInstance(CacheService::class)->getAbsoluteBaseDirectory();
        $resultPath = GeneralUtility::resolveBackPath($absoluteBasePath.implode('/', $parts));

        if (!str_starts_with($resultPath, $absoluteBasePath)) {
            throw new Exception('The generated filename "'.$resultPath.'" should start with the cache directory "'.$absoluteBasePath.'"', 123781);
        }

        return $resultPath;
    }

    /**
     * Check if the $requestUri is a valid base for cache identifier.
     */
    public function isValidEntryIdentifier(string $requestUri): bool
    {
        if (false === GeneralUtility::isValidUrl($requestUri)) {
            return false;
        }
        $urlParts = parse_url($requestUri);
        $required = ['host', 'path', 'scheme'];
        foreach ($required as $item) {
            if (!isset($urlParts[$item]) || mb_strlen($urlParts[$item]) <= 0) {
                return false;
            }
        }

        return true;
    }
}

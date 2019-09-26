<?php

/**
 * IdentifierBuilder
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Cache;

use SFC\Staticfilecache\Service\CacheService;
use SFC\Staticfilecache\StaticFileCacheObject;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * IdentifierBuilder
 */
class IdentifierBuilder extends StaticFileCacheObject
{

    /**
     * Get the cache name for the given URI.
     *
     * @param string $requestUri
     *
     * @throws \Exception
     * @return string
     */
    public function getCacheFilename(string $requestUri): string
    {
        if (!$this->isValidEntryIdentifier($requestUri)) {
            throw new \Exception('Invalid RequestUri as cache identifier: ' . $requestUri, 2346782);
        }
        $urlParts = \parse_url($requestUri);
        $parts = [
            $urlParts['scheme'],
            $urlParts['host'],
            isset($urlParts['port']) ? (int)$urlParts['port'] : ('https' === $urlParts['scheme'] ? 443 : 80),
        ];

        $path = \implode('/', $parts) . '/' . \trim($urlParts['path'], '/');
        $cacheFilename = GeneralUtility::makeInstance(CacheService::class)->getAbsoluteBaseDirectory() . $path;
        return \rtrim($cacheFilename, '/') . '/index';
    }

    /**
     * Check if the $requestUri is a valid base for cache identifier.
     *
     * @param string $requestUri
     *
     * @return bool
     */
    public function isValidEntryIdentifier(string $requestUri): bool
    {
        if (false === GeneralUtility::isValidUrl($requestUri)) {
            return false;
        }
        $urlParts = \parse_url($requestUri);
        $required = ['host', 'path', 'scheme'];
        foreach ($required as $item) {
            if (!isset($urlParts[$item]) || \mb_strlen($urlParts[$item]) <= 0) {
                return false;
            }
        }

        return true;
    }
}

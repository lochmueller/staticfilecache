<?php
/**
 *
 */
namespace SFC\Staticfilecache\Cache;

use SFC\Staticfilecache\Service\CacheService;
use SFC\Staticfilecache\Service\ConfigurationService;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

/**
 * IdentifierBuilder
 */
class IdentifierBuilder implements SingletonInterface
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

        $configurationService = GeneralUtility::makeInstance(ConfigurationService::class);

        $path = \implode('/', $parts) . '/' . \trim($urlParts['path'], '/');
        $cacheFilename = GeneralUtility::getFileAbsFileName(GeneralUtility::makeInstance(CacheService::class)->getRelativeBaseDirectory() . $path);
        $fileExtension = (string)PathUtility::pathinfo(PathUtility::basename($cacheFilename), PATHINFO_EXTENSION);
        $typesExtensions = 'sfc,' . $configurationService->get('fileTypes');
        if (empty($fileExtension) || !GeneralUtility::inList($typesExtensions, $fileExtension)) {
            $cacheFilename = \rtrim($cacheFilename, '/') . '/index.html';
        }

        return $cacheFilename;
    }

    /**
     * Check if the $requestUri is a valid base for cache identifier.
     *
     * @param string $requestUri
     *
     * @return bool
     */
    protected function isValidEntryIdentifier(string $requestUri): bool
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

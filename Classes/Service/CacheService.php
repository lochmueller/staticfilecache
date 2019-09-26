<?php

/**
 * Cache Service.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Service;

use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * Cache Service.
 */
class CacheService extends AbstractService
{
    /**
     * Get the StaticFileCache.
     *
     * @throws NoSuchCacheException
     *
     * @return VariableFrontend
     */
    public function get(): VariableFrontend
    {
        return $this->getManager()->getCache('staticfilecache');
    }

    /**
     * Get the cache manager.
     *
     * @return CacheManager
     */
    public function getManager(): CacheManager
    {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        return $objectManager->get(CacheManager::class);
    }

    /**
     * Clear cache by page ID.
     *
     * @param int $pageId
     *
     * @throws NoSuchCacheException
     */
    public function clearByPageId(int $pageId)
    {
        $cache = $this->get();
        $cacheEntries = \array_keys($cache->getByTag('pageId_' . $pageId));
        foreach ($cacheEntries as $cacheEntry) {
            $cache->remove($cacheEntry);
        }
    }

    /**
     * Get absolute base directory
     *
     * @return string
     */
    public function getAbsoluteBaseDirectory(): string
    {
        $relativeDirectory = 'typo3temp/tx_staticfilecache/';
        $overrideDirectory = trim((string) GeneralUtility::makeInstance(ConfigurationService::class)->get('overrideCacheDirectory'));
        if ($overrideDirectory !== '') {
            $relativeDirectory = rtrim($overrideDirectory, '/') . '/';
        }

        $absolutePath = Environment::getPublicPath() . '/' . $relativeDirectory;
        return GeneralUtility::resolveBackPath($absolutePath);
    }
}

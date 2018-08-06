<?php

/**
 * Cache Service.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Service;

use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Cache Service.
 */
class CacheService extends AbstractService
{
    /**
     * Get the static file cache.
     *
     * @return FrontendInterface
     */
    public function getCache(): FrontendInterface
    {
        /** @var CacheManager $cacheManager */
        $objectManager = new ObjectManager();
        $cacheManager = $objectManager->get(CacheManager::class);

        return $cacheManager->getCache('staticfilecache');
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
        $cache = $this->getCache();
        $cacheEntries = \array_keys($cache->getByTag('pageId_' . $pageId));
        foreach ($cacheEntries as $cacheEntry) {
            $cache->remove($cacheEntry);
        }
    }
}

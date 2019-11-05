<?php

/**
 * Cache Service.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Service;

use SFC\Staticfilecache\Domain\Repository\QueueRepository;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

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
     * Get absolute base directory incl. ending slash
     *
     * @return string
     */
    public function getAbsoluteBaseDirectory(): string
    {
        $relativeDirectory = 'typo3temp/tx_staticfilecache/';
        $overrideDirectory = trim((string)GeneralUtility::makeInstance(ConfigurationService::class)->get('overrideCacheDirectory'));
        if ($overrideDirectory !== '') {
            $relativeDirectory = rtrim($overrideDirectory, '/') . '/';
        }

        $absolutePath = Environment::getPublicPath() . '/' . $relativeDirectory;
        return GeneralUtility::resolveBackPath($absolutePath);
    }

    /**
     * Flush the cache
     *
     * @param bool $includeBoostQueue
     * @throws NoSuchCacheException
     *
     * @throws \TYPO3\CMS\Core\Cache\Exception\NoSuchCacheGroupException
     */
    public function flush(bool $includeBoostQueue = false)
    {
        if ($includeBoostQueue) {
            \define('SFC_QUEUE_WORKER', true);
        }
        $this->get()->flush();
        $this->getManager()->flushCachesInGroup('pages');

        if ($includeBoostQueue) {
            GeneralUtility::makeInstance(QueueRepository::class)->truncate();
        }
    }
}

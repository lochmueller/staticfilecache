<?php

/**
 * Cache commands.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Command;

use SFC\Staticfilecache\Service\CacheService;
use SFC\Staticfilecache\Service\QueueService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Cache commands.
 */
class CacheCommandController extends AbstractCommandController
{
    /**
     * Remove the expired pages.
     *
     * @throws \TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException
     */
    public function removeExpiredPagesCommand()
    {
        GeneralUtility::makeInstance(CacheService::class)->get()->collectGarbage();
    }

    /**
     * Run the cache boost queue.
     *
     * @param int $limitItems Limit the items that are crawled. 0 => all
     */
    public function runCacheBoostQueueCommand($limitItems = 0)
    {
        $queue = GeneralUtility::makeInstance(QueueService::class);
        $queue->run($limitItems);
    }

    /**
     * Run the cache boost queue.
     */
    public function cleanupCacheBoostQueueCommand()
    {
        $queue = GeneralUtility::makeInstance(QueueService::class);
        $queue->cleanup();
    }

    /**
     * Flush the cache
     * If the boost mode is active, all pages are recrawlt.
     *
     * @param bool $forceBoostModeFlush
     *
     * @throws \TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException
     * @throws \TYPO3\CMS\Core\Cache\Exception\NoSuchCacheGroupException
     */
    public function flushCacheCommand($forceBoostModeFlush = false)
    {
        if ($forceBoostModeFlush) {
            \define('SFC_QUEUE_WORKER', true);
        }
        /** @var CacheService $cacheService */
        $cacheService = GeneralUtility::makeInstance(CacheService::class);
        $cacheService->get()->flush();
        $cacheService->getManager()->flushCachesInGroup('pages');
    }
}

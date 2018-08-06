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
     */
    public function removeExpiredPagesCommand()
    {
        GeneralUtility::makeInstance(CacheService::class)->getCache()->collectGarbage();
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
     */
    public function flushCacheCommand($forceBoostModeFlush = false)
    {
        if ($forceBoostModeFlush) {
            \define('SFC_QUEUE_WORKER', true);
        }
        GeneralUtility::makeInstance(CacheService::class)->getCache()->flush();
    }
}

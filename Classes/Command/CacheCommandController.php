<?php

/**
 * Cache commands.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Command;

use SFC\Staticfilecache\Service\CacheService;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;

/**
 * Cache commands.
 *
 * @deprecated
 */
class CacheCommandController extends CommandController
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
     * @param int $limitItems          Limit the items that are crawled. 0 => all
     * @param int $stopProcessingAfter Stop crawling new items after N seconds since scheduler task started. 0 => infinite
     */
    public function runCacheBoostQueueCommand($limitItems = 0, $stopProcessingAfter = 0)
    {
        $input = new StringInput('');
        $output = new BufferedOutput();

        $queue = GeneralUtility::makeInstance(BoostQueueRunCommand::class);
        $queue->run($input, $output);
    }

    /**
     * Run the cache boost queue.
     */
    public function cleanupCacheBoostQueueCommand()
    {
        $input = new StringInput('');
        $output = new BufferedOutput();

        $queue = GeneralUtility::makeInstance(BoostQueueCleanupCommand::class);
        $queue->run($input, $output);
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

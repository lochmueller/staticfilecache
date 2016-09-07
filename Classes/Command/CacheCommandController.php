<?php
/**
 * Cache commands
 *
 * @author  Tim Lochmüller
 */

namespace SFC\Staticfilecache\Command;

use SFC\Staticfilecache\QueueManager;
use SFC\Staticfilecache\Utility\CacheUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;

/**
 * Cache commands
 *
 * @author Tim Lochmüller
 */
class CacheCommandController extends CommandController
{

    /**
     * Remove the expired pages
     */
    public function removeExpiredPagesCommand()
    {
        CacheUtility::getCache()
            ->collectGarbage();
    }

    /**
     * Run the cache boost queue
     */
    public function runCacheBoostQueueCommand()
    {
        $queue = GeneralUtility::makeInstance(QueueManager::class);
        $queue->run();
    }
}

<?php
/**
 * Cache commands
 *
 * @package SFC\NcStaticfilecache\Command
 * @author  Tim Lochmüller
 */

namespace SFC\NcStaticfilecache\Command;

use SFC\NcStaticfilecache\Utility\CacheUtility;
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
}

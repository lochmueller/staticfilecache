<?php
/**
 * Crawler hook
 *
 * @package SFC\NcStaticfilecache\Hook
 * @author  Tim Lochmüller
 */

namespace SFC\NcStaticfilecache\Hook;

use SFC\NcStaticfilecache\Utility\CacheUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Crawler hook
 *
 * @author         Tim Lochmüller
 * @author         Daniel Poetzinger
 */
class Crawler
{

    /**
     * (Hook-function called from TypoScriptFrontend, see ext_localconf.php for configuration)
     *
     * @param array $parameters Parameters delivered by TypoScriptFrontend
     * @param TypoScriptFrontendController $pObj The calling parent object (TypoScriptFrontend)
     *
     * @returnvoid
     */
    public function clearStaticFile(array $parameters, TypoScriptFrontendController $pObj)
    {
        if (ExtensionManagementUtility::isLoaded('crawler') && $pObj->applicationData['tx_crawler']['running'] && in_array('tx_ncstaticfilecache_clearstaticfile',
                $pObj->applicationData['tx_crawler']['parameters']['procInstructions'])
        ) {
            $pageId = $GLOBALS['TSFE']->id;
            if (is_numeric($pageId)) {
                CacheUtility::clearByPageId($pageId);
                $pObj->applicationData['tx_crawler']['log'][] = 'EXT:nc_staticfilecache cleared static file';
            } else {
                $pObj->applicationData['tx_crawler']['log'][] = 'EXT:nc_staticfilecache skipped';
            }
        }
    }
}

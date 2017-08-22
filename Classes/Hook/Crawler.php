<?php
/**
 * Crawler hook.
 */
declare(strict_types=1);

namespace SFC\Staticfilecache\Hook;

use SFC\Staticfilecache\Service\CacheService;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Crawler hook.
 */
class Crawler extends AbstractHook
{
    /**
     * (Hook-function called from TypoScriptFrontend, see ext_localconf.php for configuration).
     *
     * @param array                        $parameters   Parameters delivered by TypoScriptFrontend
     * @param TypoScriptFrontendController $parentObject The calling parent object (TypoScriptFrontend)
     */
    public function clearStaticFile(array $parameters, TypoScriptFrontendController $parentObject)
    {
        if (!ExtensionManagementUtility::isLoaded('crawler')) {
            return;
        }
        if ($parentObject->applicationData['tx_crawler']['running'] && in_array(
            'tx_staticfilecache_clearstaticfile',
            $parentObject->applicationData['tx_crawler']['parameters']['procInstructions']
        )
        ) {
            $this->clearCache($parentObject);
        }
    }

    /**
     * Execute the clear cache.
     *
     * @param TypoScriptFrontendController $parentObject
     */
    protected function clearCache(TypoScriptFrontendController $parentObject)
    {
        $pageId = $parentObject->id;
        if (!is_numeric($pageId)) {
            $parentObject->applicationData['tx_crawler']['log'][] = 'EXT:staticfilecache skipped';

            return;
        }

        GeneralUtility::makeInstance(CacheService::class)->clearByPageId($pageId);
        $parentObject->applicationData['tx_crawler']['log'][] = 'EXT:staticfilecache cleared static file';
    }
}

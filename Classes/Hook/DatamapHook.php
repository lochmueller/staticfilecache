<?php

declare(strict_types=1);

/**
 * DatamapHook.
 */

namespace SFC\Staticfilecache\Hook;

use SFC\Staticfilecache\Service\CacheService;
use SFC\Staticfilecache\Service\ConfigurationService;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * DatamapHook.
 */
class DatamapHook extends AbstractHook
{
    /**
     * Check if the page is removed out of the SFC.
     * We drop the cache in this case.
     *
     * @param $status
     * @param $table
     * @param $id
     * @param $fieldArray
     */
    public function processDatamap_afterDatabaseOperations($status, $table, $id, $fieldArray, DataHandler $dataHandler): void
    {
        if ('pages' !== $table || !MathUtility::canBeInterpretedAsInteger($id)) {
            return;
        }

        $row = BackendUtility::getRecord($table, (int) $id);
        $allowSfc = (bool) $row['tx_staticfilecache_cache'];
        if (!$allowSfc) {
            try {
                // Delete cache
                $configuration = GeneralUtility::makeInstance(ConfigurationService::class);
                $configuration->override('boostMode', '0');
                $cacheService = GeneralUtility::makeInstance(CacheService::class);
                $cacheService->get()->flushByTag('pageId_'.$id);
                $configuration->reset('boostMode');
            } catch (\Exception $ex) {
                return;
            }
        }
    }
}

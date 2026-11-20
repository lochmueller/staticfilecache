<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Hook;

use SFC\Staticfilecache\Service\CacheService;
use SFC\Staticfilecache\Service\ConfigurationService;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

class DatamapHook
{
    /**
     * Check if the page is removed out of the SFC.
     * We drop the cache in this case.
     *
     * @param array<string, mixed> $fieldArray
     */
    public function processDatamap_afterDatabaseOperations(string $status, string $table, int|string $id, array $fieldArray, DataHandler $dataHandler): void
    {
        if ('pages' !== $table) {
            return;
        }
        if (!MathUtility::canBeInterpretedAsInteger($id)) {
            return;
        }

        // A deleted page has no record anymore and must not stay in the cache either
        $row = $this->getPageRecord((int) $id);
        $allowSfc = (bool) ($row['tx_staticfilecache_cache'] ?? false);
        if (!$allowSfc) {
            try {
                // Delete right now!! do not wait until queue is deleting this
                $configuration = GeneralUtility::makeInstance(ConfigurationService::class);
                $configuration->override('boostMode', '0');
                /** @var CacheService $cacheService */
                $cacheService = GeneralUtility::makeInstance(CacheService::class);
                $cacheService->get()->flushByTag('pageId_' . $id);
                $configuration->reset('boostMode');
            } catch (\Exception $ex) {
                return;
            }
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function getPageRecord(int $id): ?array
    {
        return BackendUtility::getRecord('pages', $id);
    }
}

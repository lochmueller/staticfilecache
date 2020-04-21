<?php

/**
 * UninstallProcess.
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Hook;

use SFC\Staticfilecache\Service\CacheService;
use SFC\Staticfilecache\Service\ConfigurationService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extensionmanager\Utility\InstallUtility;

/**
 * UninstallProcess.
 */
class UninstallProcess extends AbstractHook
{
    /**
     * Check if staticfile cache is deactived and drop the current cache.
     *
     * @param string         $extensionKey
     * @param InstallUtility $installUtility
     *
     * @throws \TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException
     *
     * @return array
     */
    public function afterExtensionUninstall(string $extensionKey, InstallUtility $installUtility)
    {
        $configuration = GeneralUtility::makeInstance(ConfigurationService::class);
        $configuration->override('boostMode', '0');

        $cacheService = GeneralUtility::makeInstance(CacheService::class);
        $cacheService->get()->flush();

        return [$extensionKey, $installUtility];
    }
}

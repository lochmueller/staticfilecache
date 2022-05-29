<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\EventListener;

use SFC\Staticfilecache\Service\CacheService;
use SFC\Staticfilecache\Service\ConfigurationService;
use TYPO3\CMS\Core\Package\Event\AfterPackageDeactivationEvent;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class AfterPackageDeactivationListener
{
    public function __invoke(AfterPackageDeactivationEvent $event): void
    {
        $configuration = GeneralUtility::makeInstance(ConfigurationService::class);
        $configuration->override('boostMode', '0');

        $cacheService = GeneralUtility::makeInstance(CacheService::class);
        $cacheService->get()->flush();
    }
}

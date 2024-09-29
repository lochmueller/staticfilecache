<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\EventListener;

use SFC\Staticfilecache\Service\CacheService;
use SFC\Staticfilecache\Service\ConfigurationService;
use TYPO3\CMS\Core\Package\Event\AfterPackageDeactivationEvent;

class AfterPackageDeactivationListener
{
    public function __construct(
        readonly private ConfigurationService $configurationService,
        readonly private CacheService         $cacheService,
    ) {}

    public function __invoke(AfterPackageDeactivationEvent $event): void
    {
        $this->configurationService->override('boostMode', '0');
        $this->cacheService->get()->flush();
    }
}

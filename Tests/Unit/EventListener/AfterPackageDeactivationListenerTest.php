<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\EventListener;

use SFC\Staticfilecache\EventListener\AfterPackageDeactivationListener;
use SFC\Staticfilecache\Service\CacheService;
use SFC\Staticfilecache\Tests\Unit\AbstractTest;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Package\Event\AfterPackageDeactivationEvent;

/**
 * AfterPackageDeactivationListenerTest.
 *
 * @internal
 * @coversNothing
 */
final class AfterPackageDeactivationListenerTest extends AbstractTest
{
    public function testDisableBoostModeAndFlushCache(): void
    {
        $configurationService = $this->setExtensionConfiguration(['boostMode' => '1']);

        $cache = $this->createMock(FrontendInterface::class);
        $cache->expects(self::once())->method('flush');
        $cacheService = $this->createMock(CacheService::class);
        $cacheService->expects(self::once())->method('get')->willReturn($cache);

        $listener = new AfterPackageDeactivationListener($configurationService, $cacheService);
        $listener(new AfterPackageDeactivationEvent('staticfilecache', 'typo3-cms-extension'));

        self::assertSame('0', $configurationService->get('boostMode'));
    }

    public function testIgnoreOtherPackages(): void
    {
        $configurationService = $this->setExtensionConfiguration(['boostMode' => '1']);

        $cacheService = $this->createMock(CacheService::class);
        $cacheService->expects(self::never())->method('get');

        $listener = new AfterPackageDeactivationListener($configurationService, $cacheService);
        $listener(new AfterPackageDeactivationEvent('news', 'typo3-cms-extension'));

        self::assertSame('1', $configurationService->get('boostMode'));
    }
}

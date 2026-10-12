<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Service;

use SFC\Staticfilecache\Domain\Repository\QueueRepository;
use SFC\Staticfilecache\Service\CacheService;
use SFC\Staticfilecache\Tests\Unit\AbstractTest;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * CacheServiceTest.
 *
 * @internal
 * @coversNothing
 */
final class CacheServiceTest extends AbstractTest
{
    public function testDefaultAbsoluteBaseDirectory(): void
    {
        $this->setExtensionConfiguration();

        self::assertSame(
            Environment::getPublicPath() . '/typo3temp/assets/tx_staticfilecache/',
            (new CacheService())->getAbsoluteBaseDirectory()
        );
    }

    public function testOverrideAbsoluteBaseDirectory(): void
    {
        $this->setExtensionConfiguration(['overrideCacheDirectory' => ' custom/../sfc// ']);

        self::assertSame(
            Environment::getPublicPath() . '/sfc/',
            (new CacheService())->getAbsoluteBaseDirectory()
        );
    }

    public function testGetCache(): void
    {
        $cache = $this->createStub(FrontendInterface::class);
        $cacheManager = $this->createMock(CacheManager::class);
        $cacheManager->expects(self::once())->method('getCache')->with('staticfilecache')->willReturn($cache);
        GeneralUtility::setSingletonInstance(CacheManager::class, $cacheManager);

        $cacheService = new CacheService();

        self::assertSame($cache, $cacheService->get());
        self::assertSame($cacheManager, $cacheService->getManager());
    }

    public function testFlush(): void
    {
        $configurationService = $this->setExtensionConfiguration(['boostMode' => '1']);
        $this->getCacheManagerExpectingFlush();

        (new CacheService())->flush();

        self::assertSame('1', $configurationService->get('boostMode'));
    }

    public function testFlushIncludingBoostQueue(): void
    {
        $configurationService = $this->setExtensionConfiguration(['boostMode' => '1']);
        $this->getCacheManagerExpectingFlush();

        $queueRepository = $this->createMock(QueueRepository::class);
        $queueRepository->expects(self::once())->method('truncate');
        GeneralUtility::addInstance(QueueRepository::class, $queueRepository);

        (new CacheService())->flush(true);

        self::assertSame('0', $configurationService->get('boostMode'), 'Boost mode should be disabled while flushing');
    }

    protected function getCacheManagerExpectingFlush(): CacheManager
    {
        $cache = $this->createMock(FrontendInterface::class);
        $cache->expects(self::once())->method('flush');

        $cacheManager = $this->createMock(CacheManager::class);
        $cacheManager->method('getCache')->willReturn($cache);
        $cacheManager->expects(self::once())->method('flushCachesInGroup')->with('pages');
        GeneralUtility::setSingletonInstance(CacheManager::class, $cacheManager);

        return $cacheManager;
    }
}

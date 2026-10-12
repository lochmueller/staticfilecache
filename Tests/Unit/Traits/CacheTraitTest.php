<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Traits;

use Psr\Log\LoggerInterface;
use SFC\Staticfilecache\Tests\Unit\AbstractTest;
use SFC\Staticfilecache\Traits\CacheTrait;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * CacheTraitTest.
 *
 * @internal
 * @coversNothing
 */
final class CacheTraitTest extends AbstractTest
{
    public function testCacheRunTimeReturnsCachedValue(): void
    {
        $cache = $this->createMock(FrontendInterface::class);
        $cache->expects(self::once())->method('get')->with('my_key')->willReturn('cached');
        $cache->expects(self::never())->method('set');
        $this->registerCache('runtime', $cache);

        $result = $this->getSubject()->runTime('my_key', static fn(): string => 'fresh');

        self::assertSame('cached', $result);
    }

    public function testCacheRunTimeStoresCallbackResultWithoutLifetime(): void
    {
        $cache = $this->createMock(FrontendInterface::class);
        $cache->method('get')->willReturn(false);
        $cache->expects(self::once())->method('set')->with('my_key', 'fresh', [], null);
        $this->registerCache('runtime', $cache);

        $result = $this->getSubject()->runTime('my_key', static fn(): string => 'fresh');

        self::assertSame('fresh', $result);
    }

    public function testCacheLongTimeUsePrefixLifetimeAndTags(): void
    {
        $cache = $this->createMock(FrontendInterface::class);
        $cache->expects(self::once())->method('get')->with('sfc_my_key')->willReturn(false);
        $cache->expects(self::once())->method('set')->with('sfc_my_key', ['data'], ['tag1'], 120);
        $this->registerCache('pagesection', $cache);

        $result = $this->getSubject()->longTime('my_key', static fn(): array => ['data'], 120, ['tag1']);

        self::assertSame(['data'], $result);
    }

    public function testCacheRemoteUriFetchTwiceIfFirstResultIsEmpty(): void
    {
        $cache = $this->createMock(FrontendInterface::class);
        $cache->expects(self::exactly(2))->method('get')->willReturnOnConsecutiveCalls(false, 'typo3temp/remote/a/b/c/file.png');
        $cache->expects(self::once())->method('set')->with('https://example.com/file.png', null, [], 3600);
        $this->registerCache('remote_file', $cache);

        $result = $this->getSubject()->remoteUri('https://example.com/file.png');

        self::assertSame('typo3temp/remote/a/b/c/file.png', $result);
    }

    public function testMissingCacheLogErrorAndCallCallback(): void
    {
        $cacheManager = $this->createStub(CacheManager::class);
        $cacheManager->method('getCache')->willThrowException(new NoSuchCacheException('Missing', 1));
        GeneralUtility::setSingletonInstance(CacheManager::class, $cacheManager);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error')->with('Missing');
        $logManager = $this->createStub(LogManager::class);
        $logManager->method('getLogger')->willReturn($logger);
        GeneralUtility::setSingletonInstance(LogManager::class, $logManager);

        $result = $this->getSubject()->runTime('my_key', static fn(): string => 'fresh');

        self::assertSame('fresh', $result);
    }

    private function registerCache(string $identifier, FrontendInterface $cache): void
    {
        $cacheManager = $this->createMock(CacheManager::class);
        $cacheManager->expects(self::atLeastOnce())->method('getCache')->with($identifier)->willReturn($cache);
        GeneralUtility::setSingletonInstance(CacheManager::class, $cacheManager);
    }

    private function getSubject(): object
    {
        return new class {
            use CacheTrait;

            public function runTime(string $entryIdentifier, callable $callback): mixed
            {
                return $this->cacheRunTime($entryIdentifier, $callback);
            }

            public function longTime(string $entryIdentifier, callable $callback, int $lifetime, array $tags): mixed
            {
                return $this->cacheLongTime($entryIdentifier, $callback, $lifetime, $tags);
            }

            public function remoteUri(string $entryIdentifier): mixed
            {
                return $this->cacheRemoteUri($entryIdentifier);
            }
        };
    }
}

<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Hook;

use SFC\Staticfilecache\Hook\DatamapHook;
use SFC\Staticfilecache\Service\CacheService;
use SFC\Staticfilecache\Service\ConfigurationService;
use SFC\Staticfilecache\Tests\Unit\AbstractTest;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * DatamapHookTest.
 *
 * @internal
 * @coversNothing
 */
final class DatamapHookTest extends AbstractTest
{
    /**
     * @return array<string, array{string, int|string}>
     */
    public static function ignoredRecordsDataProvider(): array
    {
        return [
            'other table' => ['tt_content', 1],
            'new page' => ['pages', 'NEW1234'],
            'non numeric id' => ['pages', 'abc'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('ignoredRecordsDataProvider')]
    public function testIgnoredRecordsDoNotFlushCache(string $table, int|string $id): void
    {
        $configurationService = $this->createMock(ConfigurationService::class);
        $configurationService->expects(self::never())->method('override');
        GeneralUtility::setSingletonInstance(ConfigurationService::class, $configurationService);

        $cacheService = $this->createMock(CacheService::class);
        $cacheService->expects(self::never())->method('get');
        GeneralUtility::addInstance(CacheService::class, $cacheService);

        $dataHandler = (new \ReflectionClass(DataHandler::class))->newInstanceWithoutConstructor();

        (new DatamapHook())->processDatamap_afterDatabaseOperations('update', $table, $id, [], $dataHandler);

        // Remove the unused prototype instance again
        GeneralUtility::purgeInstances();
    }

    /**
     * @return array<string, array{array<string, mixed>|null, bool}>
     */
    public static function pageRecordsDataProvider(): array
    {
        return [
            'cache allowed' => [['tx_staticfilecache_cache' => 1], false],
            'cache disallowed' => [['tx_staticfilecache_cache' => 0], true],
            // e.g. a page which is updated after it was deleted
            'deleted page' => [null, true],
        ];
    }

    /**
     * @param array<string, mixed>|null $pageRecord
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('pageRecordsDataProvider')]
    public function testCacheIsFlushedIfPageIsNotCacheable(?array $pageRecord, bool $expectFlush): void
    {
        $configurationService = $this->createMock(ConfigurationService::class);
        $configurationService->expects($expectFlush ? self::once() : self::never())->method('override')->with('boostMode', '0');
        GeneralUtility::setSingletonInstance(ConfigurationService::class, $configurationService);

        $cache = $this->createMock(FrontendInterface::class);
        $cache->expects($expectFlush ? self::once() : self::never())->method('flushByTag')->with('pageId_42');
        $cacheService = $this->createStub(CacheService::class);
        $cacheService->method('get')->willReturn($cache);
        GeneralUtility::addInstance(CacheService::class, $cacheService);

        $dataHandler = (new \ReflectionClass(DataHandler::class))->newInstanceWithoutConstructor();

        // TYPO3 turns warnings into exceptions, so a warning has to fail the test
        set_error_handler(
            static function (int $severity, string $message, string $file, int $line): never {
                throw new \ErrorException($message, 0, $severity, $file, $line);
            },
        );

        try {
            (new TestableDatamapHook($pageRecord))->processDatamap_afterDatabaseOperations('update', 'pages', 42, [], $dataHandler);
        } finally {
            restore_error_handler();
            GeneralUtility::purgeInstances();
        }
    }
}

final class TestableDatamapHook extends DatamapHook
{
    /**
     * @param array<string, mixed>|null $pageRecord
     */
    public function __construct(private readonly ?array $pageRecord) {}

    protected function getPageRecord(int $id): ?array
    {
        return $this->pageRecord;
    }
}

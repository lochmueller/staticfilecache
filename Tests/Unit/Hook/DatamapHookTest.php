<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Hook;

use SFC\Staticfilecache\Hook\DatamapHook;
use SFC\Staticfilecache\Service\CacheService;
use SFC\Staticfilecache\Service\ConfigurationService;
use SFC\Staticfilecache\Tests\Unit\AbstractTest;
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
}

<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Command;

use SFC\Staticfilecache\Command\FlushCacheCommand;
use SFC\Staticfilecache\Service\CacheService;
use SFC\Staticfilecache\Tests\Unit\AbstractTest;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * FlushCacheCommandTest.
 *
 * @internal
 * @coversNothing
 */
final class FlushCacheCommandTest extends AbstractTest
{
    public function testFlushWithoutBoostMode(): void
    {
        $cacheService = $this->createMock(CacheService::class);
        $cacheService->expects(self::once())->method('flush')->with(false);
        GeneralUtility::addInstance(CacheService::class, $cacheService);

        $tester = new CommandTester(new FlushCacheCommand());

        self::assertSame(Command::SUCCESS, $tester->execute([]));
    }

    public function testFlushWithForcedBoostModeFlush(): void
    {
        $cacheService = $this->createMock(CacheService::class);
        $cacheService->expects(self::once())->method('flush')->with(true);
        GeneralUtility::addInstance(CacheService::class, $cacheService);

        $tester = new CommandTester(new FlushCacheCommand());

        self::assertSame(Command::SUCCESS, $tester->execute(['--force-boost-mode-flush' => true]));
    }
}

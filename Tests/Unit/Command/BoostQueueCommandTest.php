<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Command;

use PHPUnit\Framework\MockObject\MockObject;
use SFC\Staticfilecache\Command\BoostQueueCommand;
use SFC\Staticfilecache\Domain\Repository\QueueRepository;
use SFC\Staticfilecache\Service\QueueService;
use SFC\Staticfilecache\Tests\Unit\AbstractTest;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * BoostQueueCommandTest.
 *
 * @internal
 * @coversNothing
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
final class BoostQueueCommandTest extends AbstractTest
{
    protected QueueRepository&MockObject $queueRepository;

    protected QueueService&MockObject $queueService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->queueRepository = $this->createMock(QueueRepository::class);
        $this->queueService = $this->createMock(QueueService::class);
    }

    public function testDescriptionIsPrefixed(): void
    {
        $command = $this->getCommand();
        $command->setDescription('Boost');

        self::assertSame('StaticFileCache task: Boost', $command->getDescription());
    }

    public function testConfiguredOptions(): void
    {
        $definition = $this->getCommand()->getDefinition();

        self::assertSame(500, $definition->getOption('limit-items')->getDefault());
        self::assertSame(240, $definition->getOption('stop-processing-after')->getDefault());
        self::assertFalse($definition->getOption('avoid-cleanup')->acceptValue());
        self::assertSame(50, $definition->getOption('batch-size')->getDefault());
        self::assertSame(10, $definition->getOption('concurrency')->getDefault());
    }

    public function testEmptyQueue(): void
    {
        $this->queueRepository->method('countOpen')->willReturn(0);
        $this->queueRepository->expects(self::once())->method('findOpenBatch')->with(50)->willReturn([]);
        $this->queueService->expects(self::never())->method('runBatchRequests');
        $this->queueRepository->expects(self::never())->method('findOldBatch');

        $tester = $this->execute(['--avoid-cleanup' => true]);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode());
        $display = $tester->getDisplay();
        self::assertStringContainsString('Found 0 items in queue. Will process up to 500 items with batch size 50.', $display);
        self::assertStringContainsString('No more items in queue.', $display);
        self::assertStringContainsString('Success: 0, Failed: 0', $display);
    }

    public function testProcessMultipleBatches(): void
    {
        $this->queueRepository->method('countOpen')->willReturn(3);
        $this->queueRepository->expects(self::exactly(2))
            ->method('findOpenBatch')
            ->with(2)
            ->willReturnOnConsecutiveCalls([$this->entry(1), $this->entry(2)], [$this->entry(3)]);

        $this->queueService->expects(self::exactly(2))
            ->method('runBatchRequests')
            ->with(self::isArray(), 4)
            ->willReturnOnConsecutiveCalls([200, 500], [200]);

        $tester = $this->execute(['--batch-size' => '2', '--concurrency' => '4', '--avoid-cleanup' => true]);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode());
        $display = $tester->getDisplay();
        self::assertStringContainsString('Processed batch of 2 items (2 total, 1 successful, 1 failed).', $display);
        self::assertStringContainsString('Processed batch of 1 items (3 total, 2 successful, 1 failed).', $display);
        self::assertStringContainsString('Success: 2, Failed: 1', $display);
    }

    public function testStopAtLimit(): void
    {
        $this->queueRepository->method('countOpen')->willReturn(10);
        // Batch size is reduced to the total limit
        $this->queueRepository->expects(self::once())
            ->method('findOpenBatch')
            ->with(2)
            ->willReturn([$this->entry(1), $this->entry(2)]);
        $this->queueService->method('runBatchRequests')->willReturn([200, 200]);

        $tester = $this->execute(['--limit-items' => '2', '--avoid-cleanup' => true]);

        self::assertStringContainsString('Will process up to 2 items with batch size 2.', $tester->getDisplay());
        self::assertStringContainsString('Success: 2, Failed: 0', $tester->getDisplay());
    }

    public function testInvalidOptionsFallbackToDefaults(): void
    {
        $this->queueRepository->method('countOpen')->willReturn(0);
        $this->queueRepository->expects(self::once())->method('findOpenBatch')->with(50)->willReturn([$this->entry(1)]);
        $this->queueService->expects(self::once())->method('runBatchRequests')->with(self::isArray(), 10)->willReturn([200]);

        $tester = $this->execute([
            '--limit-items' => '0',
            '--batch-size' => '-1',
            '--concurrency' => '0',
            '--avoid-cleanup' => true,
        ]);

        self::assertStringContainsString('Will process up to 5000 items with batch size 50.', $tester->getDisplay());
    }

    public function testCleanupQueue(): void
    {
        $this->queueRepository->method('findOpenBatch')->willReturn([]);

        $fullBatch = array_map(fn(int $uid): array => $this->entry($uid), range(1, 1000));
        $this->queueRepository->expects(self::exactly(2))
            ->method('findOldBatch')
            ->with(1000)
            ->willReturnOnConsecutiveCalls($fullBatch, [$this->entry(1001)]);

        $deletedUids = [];
        $this->queueRepository->expects(self::exactly(2))
            ->method('bulkDelete')
            ->willReturnCallback(static function (array $uids) use (&$deletedUids): int {
                $deletedUids = array_merge($deletedUids, $uids);

                return \count($uids);
            });

        $tester = $this->execute();

        self::assertSame(range(1, 1001), $deletedUids);
        self::assertStringContainsString('1001 items removed in', $tester->getDisplay());
    }

    public function testCleanupWithEmptyOldBatch(): void
    {
        $this->queueRepository->method('findOpenBatch')->willReturn([]);
        $this->queueRepository->expects(self::once())->method('findOldBatch')->willReturn([]);
        $this->queueRepository->expects(self::never())->method('bulkDelete');

        $tester = $this->execute();

        self::assertStringContainsString('0 items removed in', $tester->getDisplay());
    }

    public function testProcessAllEntriesOfTheQueue(): void
    {
        $connection = $this->createDatabase(
            'CREATE TABLE tx_staticfilecache_queue (
                uid INTEGER PRIMARY KEY,
                cache_url TEXT NOT NULL,
                cache_priority INTEGER DEFAULT 0 NOT NULL,
                page_uid INTEGER DEFAULT 0 NOT NULL,
                invalid_date INTEGER DEFAULT 0 NOT NULL,
                call_date INTEGER DEFAULT 0 NOT NULL,
                call_result TEXT NOT NULL
            )'
        );
        for ($i = 1; $i <= 7; $i++) {
            $connection->insert('tx_staticfilecache_queue', ['cache_url' => 'https://example.com/' . $i, 'cache_priority' => $i, 'call_result' => '']);
        }

        $repository = new QueueRepository();
        $calledUrls = [];
        $this->queueService->method('runBatchRequests')->willReturnCallback(static function (array $entries) use (&$calledUrls, $repository): array {
            $calledUrls = [...$calledUrls, ...array_column($entries, 'cache_url')];
            // Mark the entries as processed (like the real QueueService)
            $repository->bulkUpdate(array_map(static fn(array $entry): array => ['uid' => $entry['uid'], 'call_date' => time(), 'call_result' => 200], $entries));

            return array_fill(0, \count($entries), 200);
        });

        $tester = new CommandTester(new BoostQueueCommand($repository, $this->queueService));
        $tester->execute(['--batch-size' => '2', '--avoid-cleanup' => true]);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode());
        self::assertSame(['https://example.com/7', 'https://example.com/6', 'https://example.com/5', 'https://example.com/4', 'https://example.com/3', 'https://example.com/2', 'https://example.com/1'], $calledUrls);
        self::assertSame(0, $repository->countOpen());
        self::assertStringContainsString('Success: 7, Failed: 0', $tester->getDisplay());
    }

    protected function entry(int $uid): array
    {
        return ['uid' => $uid, 'cache_url' => 'https://example.com/' . $uid, 'page_uid' => $uid];
    }

    protected function execute(array $input = []): CommandTester
    {
        $tester = new CommandTester($this->getCommand());
        $tester->execute($input);

        return $tester;
    }

    protected function getCommand(): BoostQueueCommand
    {
        return new BoostQueueCommand($this->queueRepository, $this->queueService);
    }
}

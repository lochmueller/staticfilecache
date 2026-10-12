<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Domain\Repository;

use SFC\Staticfilecache\Domain\Repository\QueueRepository;
use SFC\Staticfilecache\Tests\Unit\AbstractTest;
use TYPO3\CMS\Core\Database\Connection;

/**
 * QueueRepositoryTest.
 *
 * @internal
 * @coversNothing
 */
final class QueueRepositoryTest extends AbstractTest
{
    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->createDatabase(
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
    }

    public function testFindOpenOrderedByPriority(): void
    {
        $this->addEntry('https://example.com/low', 1);
        $this->addEntry('https://example.com/high', 100);
        $this->addEntry('https://example.com/done', 500, 12345);
        $this->addEntry('https://example.com/medium', 50);

        $rows = iterator_to_array((new QueueRepository())->findOpen(2), false);

        self::assertSame(['https://example.com/high', 'https://example.com/medium'], array_column($rows, 'cache_url'));
    }

    public function testFindOpenBatch(): void
    {
        $this->addEntry('https://example.com/a', 3);
        $this->addEntry('https://example.com/b', 2);
        $this->addEntry('https://example.com/c', 1);
        $this->addEntry('https://example.com/done', 500, 12345);

        $rows = (new QueueRepository())->findOpenBatch(2, 1);

        self::assertSame(['https://example.com/b', 'https://example.com/c'], array_column($rows, 'cache_url'));
    }

    public function testCountOpen(): void
    {
        $this->addEntry('https://example.com/a');
        $this->addEntry('https://example.com/b');
        $this->addEntry('https://example.com/done', 0, 12345);

        self::assertSame(2, (new QueueRepository())->countOpen());
    }

    public function testFindOldUidsAndOldBatch(): void
    {
        $this->addEntry('https://example.com/open');
        $old1 = $this->addEntry('https://example.com/old1', 0, 12345);
        $old2 = $this->addEntry('https://example.com/old2', 0, 12346);
        $repository = new QueueRepository();

        $uids = array_map('intval', array_column(iterator_to_array($repository->findOldUids(), false), 'uid'));
        self::assertSame([$old1, $old2], $uids);

        self::assertCount(1, $repository->findOldBatch(1));
    }

    public function testFindExistingIdentifiers(): void
    {
        $this->addEntry('https://example.com/a');
        $this->addEntry('https://example.com/b', 0, 12345);
        $repository = new QueueRepository();

        self::assertSame([], $repository->findExistingIdentifiers([]));
        self::assertSame(
            ['https://example.com/a'],
            $repository->findExistingIdentifiers(['https://example.com/a', 'https://example.com/b', 'https://example.com/c'])
        );
    }

    public function testBulkInsert(): void
    {
        $repository = new QueueRepository();

        self::assertSame(0, $repository->bulkInsert([]));
        self::assertSame(2, $repository->bulkInsert([
            ['https://example.com/a', 0, 1, '', 10],
            ['https://example.com/b', 0, 1, '', 20],
        ]));
        self::assertSame(2, $repository->countOpen());
    }

    public function testStreamedBulkInsertInBatches(): void
    {
        $generator = (static function (): \Generator {
            foreach (range(1, 5) as $i) {
                yield ['https://example.com/' . $i, 0, 1, '', $i];
            }
        })();

        $inserted = (new QueueRepository())->streamedBulkInsert($generator, 2);

        self::assertSame(5, $inserted);
        self::assertSame(5, (new QueueRepository())->countOpen());
    }

    public function testStreamedBulkInsertRollbackOnError(): void
    {
        $generator = (static function (): \Generator {
            yield ['https://example.com/1', 0, 1, '', 1];
            yield ['https://example.com/2', 0, 1, '', 2];

            throw new \RuntimeException('Generator failed', 1234);
        })();

        try {
            (new QueueRepository())->streamedBulkInsert($generator, 1);
            self::fail('Exception expected');
        } catch (\RuntimeException $exception) {
            self::assertSame(1234, $exception->getCode());
        }

        self::assertSame(0, (new QueueRepository())->countOpen());
    }

    public function testBulkUpdate(): void
    {
        $uid = $this->addEntry('https://example.com/a');
        $this->addEntry('https://example.com/b');
        $repository = new QueueRepository();

        self::assertSame(0, $repository->bulkUpdate([]));
        self::assertSame(1, $repository->bulkUpdate([
            ['uid' => $uid, 'call_date' => 12345, 'call_result' => '200'],
            ['call_date' => 12345, 'call_result' => 'ignored without uid'],
        ]));

        $row = $this->connection->select(['call_date', 'call_result'], 'tx_staticfilecache_queue', ['uid' => $uid])->fetchAssociative();
        self::assertSame(['call_date' => 12345, 'call_result' => '200'], $row);
        self::assertSame(1, $repository->countOpen());
    }

    public function testBulkDelete(): void
    {
        $uid1 = $this->addEntry('https://example.com/a');
        $uid2 = $this->addEntry('https://example.com/b');
        $this->addEntry('https://example.com/c');
        $repository = new QueueRepository();

        self::assertSame(0, $repository->bulkDelete([]));
        self::assertSame(2, $repository->bulkDelete([$uid1, $uid2]));
        self::assertSame(1, $repository->countOpen());
    }

    private function addEntry(string $url, int $priority = 0, int $callDate = 0): int
    {
        $this->connection->insert('tx_staticfilecache_queue', [
            'cache_url' => $url,
            'cache_priority' => $priority,
            'call_date' => $callDate,
            'call_result' => '',
        ]);

        return (int) $this->connection->lastInsertId();
    }
}

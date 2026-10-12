<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Domain\Repository;

use SFC\Staticfilecache\Domain\Repository\AbstractRepository;
use SFC\Staticfilecache\Tests\Unit\AbstractTest;
use TYPO3\CMS\Core\Database\Connection;

/**
 * AbstractRepositoryTest.
 *
 * @internal
 * @coversNothing
 */
final class AbstractRepositoryTest extends AbstractTest
{
    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->createDatabase('CREATE TABLE tx_test (uid INTEGER PRIMARY KEY, title TEXT)');
    }

    public function testInsert(): void
    {
        $this->getRepository()->insert(['uid' => 1, 'title' => 'first']);

        self::assertSame([['uid' => 1, 'title' => 'first']], $this->fetchAll());
    }

    public function testUpdate(): void
    {
        $repository = $this->getRepository();
        $repository->insert(['uid' => 1, 'title' => 'first']);
        $repository->insert(['uid' => 2, 'title' => 'second']);

        $repository->update(['title' => 'changed'], ['uid' => 2]);

        self::assertSame([['uid' => 1, 'title' => 'first'], ['uid' => 2, 'title' => 'changed']], $this->fetchAll());
    }

    public function testDelete(): void
    {
        $repository = $this->getRepository();
        $repository->insert(['uid' => 1, 'title' => 'first']);
        $repository->insert(['uid' => 2, 'title' => 'second']);

        $repository->delete(['uid' => 1]);

        self::assertSame([['uid' => 2, 'title' => 'second']], $this->fetchAll());
    }

    public function testTruncate(): void
    {
        $repository = $this->getRepository();
        $repository->insert(['uid' => 1, 'title' => 'first']);

        $repository->truncate();

        self::assertSame([], $this->fetchAll());
    }

    private function fetchAll(): array
    {
        return $this->connection->executeQuery('SELECT uid, title FROM tx_test ORDER BY uid')->fetchAllAssociative();
    }

    private function getRepository(): AbstractRepository
    {
        return new class extends AbstractRepository {
            protected function getTableName(): string
            {
                return 'tx_test';
            }
        };
    }
}

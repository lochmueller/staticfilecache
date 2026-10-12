<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Domain\Repository;

use SFC\Staticfilecache\Domain\Repository\CacheRepository;
use SFC\Staticfilecache\Tests\Unit\AbstractTest;
use TYPO3\CMS\Core\Database\Connection;

/**
 * CacheRepositoryTest.
 *
 * @internal
 * @coversNothing
 */
final class CacheRepositoryTest extends AbstractTest
{
    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->createDatabase(
            'CREATE TABLE cache_staticfilecache (id INTEGER PRIMARY KEY, identifier TEXT, expires INTEGER, content BLOB)',
            'CREATE TABLE sfc_staticfilecache (id INTEGER PRIMARY KEY, identifier TEXT, expires INTEGER, content BLOB)',
        );
    }

    public function testFindExpiredIdentifiers(): void
    {
        $this->setExtensionConfiguration();
        $this->connection->insert('cache_staticfilecache', ['identifier' => 'https://example.com/expired', 'expires' => 1]);
        $this->connection->insert('cache_staticfilecache', ['identifier' => 'https://example.com/expired', 'expires' => 2]);
        $this->connection->insert('cache_staticfilecache', ['identifier' => 'https://example.com/valid', 'expires' => time() + 3600]);

        self::assertSame(['https://example.com/expired'], (new CacheRepository())->findExpiredIdentifiers());
    }

    public function testYieldAllIdentifiersGroupedByIdentifier(): void
    {
        $this->setExtensionConfiguration();
        $this->connection->insert('cache_staticfilecache', ['identifier' => 'https://example.com/a', 'expires' => 1]);
        $this->connection->insert('cache_staticfilecache', ['identifier' => 'https://example.com/a', 'expires' => 2]);
        $this->connection->insert('cache_staticfilecache', ['identifier' => 'https://example.com/b', 'expires' => 3]);

        $identifiers = iterator_to_array((new CacheRepository())->yieldAllIdentifiers(), false);
        sort($identifiers);

        self::assertSame(['https://example.com/a', 'https://example.com/b'], $identifiers);
    }

    public function testCountAllIdentifiersOnEmptyTable(): void
    {
        // Note: rowCount() of SELECT statements is not supported by SQLite, so only the empty table is checked
        $this->setExtensionConfiguration();

        self::assertSame(0, (new CacheRepository())->countAllIdentifiers());
    }

    public function testUseOtherTablePrefix(): void
    {
        $this->setExtensionConfiguration(['renameTablesToOtherPrefix' => '1']);
        $this->connection->insert('cache_staticfilecache', ['identifier' => 'https://example.com/default', 'expires' => 1]);
        $this->connection->insert('sfc_staticfilecache', ['identifier' => 'https://example.com/renamed', 'expires' => 1]);

        self::assertSame(['https://example.com/renamed'], iterator_to_array((new CacheRepository())->yieldAllIdentifiers(), false));
    }
}

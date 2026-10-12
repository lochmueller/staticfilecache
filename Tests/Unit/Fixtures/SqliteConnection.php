<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Fixtures;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\EmptyRestrictionContainer;

/**
 * In-memory SQLite connection for unit tests (without schema information lookups and TCA based restrictions).
 *
 * @internal
 */
class SqliteConnection extends Connection
{
    public function createQueryBuilder(): QueryBuilder
    {
        return new QueryBuilder($this, new EmptyRestrictionContainer());
    }

    protected function ensureDatabaseValueTypes(string $tableName, array &$data, array &$types): void {}
}

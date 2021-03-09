<?php

/**
 * AbstractRepository.
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Domain\Repository;

use SFC\Staticfilecache\StaticFileCacheObject;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * AbstractRepository.
 */
abstract class AbstractRepository extends StaticFileCacheObject
{
    /**
     * Delete records.
     */
    public function delete(array $identifiers): void
    {
        $this->getConnection()->delete($this->getTableName(), $identifiers);
    }

    /**
     * Truncate the table.
     */
    public function truncate(): void
    {
        $this->getConnection()->truncate($this->getTableName());
    }

    /**
     * Insert record.
     */
    public function insert(array $data): void
    {
        $this->getConnection()->insert($this->getTableName(), $data);
    }

    /**
     * Update records.
     */
    public function update(array $data, array $identifiers): void
    {
        $this->getConnection()->update(
            $this->getTableName(),
            $data,
            $identifiers
        );
    }

    /**
     * Get the table name.
     */
    abstract protected function getTableName(): string;

    /**
     * Create query.
     */
    protected function createQuery(): QueryBuilder
    {
        return $this->getConnection()->createQueryBuilder();
    }

    /**
     * Get connection.
     */
    protected function getConnection(): Connection
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($this->getTableName());
    }
}

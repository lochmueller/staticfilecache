<?php

/**
 * AbstractRepository.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Domain\Repository;

use SFC\Staticfilecache\StaticFileCacheObject;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * AbstractRepository.
 */
abstract class AbstractRepository extends StaticFileCacheObject
{
    /**
     * Delete records.
     *
     * @param array $identifiers
     */
    public function delete(array $identifiers)
    {
        $this->getConnection()->delete($this->getTableName(), $identifiers);
    }

    /**
     * Insert record.
     *
     * @param array $data
     */
    public function insert(array $data)
    {
        $this->getConnection()->insert($this->getTableName(), $data);
    }

    /**
     * Update records.
     *
     * @param array $data
     * @param array $identifiers
     */
    public function update(array $data, array $identifiers)
    {
        $this->getConnection()->update(
            $this->getTableName(),
            $data,
            $identifiers
        );
    }

    /**
     * Get the table name.
     *
     * @return string
     */
    abstract protected function getTableName(): string;

    /**
     * Create query.
     *
     * @return \TYPO3\CMS\Core\Database\Query\QueryBuilder
     */
    protected function createQuery()
    {
        return $this->getConnection()->createQueryBuilder();
    }

    /**
     * Get connection.
     *
     * @return \TYPO3\CMS\Core\Database\Connection
     */
    protected function getConnection()
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($this->getTableName());
    }
}

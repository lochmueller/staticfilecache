<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Domain\Repository;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

abstract class AbstractRepository
{
    public function delete(array $identifiers): void
    {
        $this->getConnection()->delete($this->getTableName(), $identifiers);
    }

    public function truncate(): void
    {
        $this->getConnection()->truncate($this->getTableName());
    }

    public function insert(array $data): void
    {
        $this->getConnection()->insert($this->getTableName(), $data);
    }

    public function update(array $data, array $identifiers): void
    {
        $this->getConnection()->update(
            $this->getTableName(),
            $data,
            $identifiers
        );
    }


    abstract protected function getTableName(): string;

    protected function createQuery(): QueryBuilder
    {
        return $this->getConnection()->createQueryBuilder();
    }

    protected function getConnection(): Connection
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($this->getTableName());
    }
}

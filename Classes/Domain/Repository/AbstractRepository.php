<?php

/**
 * AbstractRepository.
 */
declare(strict_types=1);

namespace SFC\Staticfilecache\Domain\Repository;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * AbstractRepository.
 */
abstract class AbstractRepository
{
    /**
     * Get the table name
     *
     * @return string
     */
    abstract protected function getTableName();

    /**
     * Create query
     *
     * @return \TYPO3\CMS\Core\Database\Query\QueryBuilder
     */
    protected function createQuery()
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($this->getTableName());
        return $connection->createQueryBuilder();
    }

}
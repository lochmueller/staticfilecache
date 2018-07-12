<?php

/**
 * CacheRepository.
 */
declare(strict_types=1);

namespace SFC\Staticfilecache\Domain\Repository;


/**
 * CacheRepository.
 */
class CacheRepository extends AbstractRepository
{
    /**
     * Get the expired cache identifiers.
     *
     * @return array
     */
    public function findExpiredIdentifiers(): array
    {
        $queryBuilder = $this->createQuery();
        $rows = $queryBuilder->select('identifier')
            ->from($this->getTableName())
            ->where($queryBuilder->expr()->lt(
                'expires',
                $queryBuilder->createNamedParameter($GLOBALS['EXEC_TIME'], \PDO::PARAM_INT)
            ))
            ->groupBy('identifier')
            ->execute()
            ->fetchAll();

        $cacheIdentifiers = [];
        foreach ($rows as $row) {
            $cacheIdentifiers[] = $row['identifier'];
        }

        return $cacheIdentifiers;
    }

    /**
     * Get all the cache identifiers.
     *
     * @return array
     */
    public function findAllIdentifiers(): array
    {
        $queryBuilder = $this->createQuery();
        $rows = $queryBuilder->select('identifier')
            ->from($this->getTableName())
            ->groupBy('identifier')
            ->execute()
            ->fetchAll();

        $cacheIdentifiers = [];
        foreach ($rows as $row) {
            $cacheIdentifiers[] = $row['identifier'];
        }

        return $cacheIdentifiers;
    }

    /**
     * Get the table name
     *
     * @return string
     */
    protected function getTableName()
    {
        return 'cf_staticfilecache';
    }
}
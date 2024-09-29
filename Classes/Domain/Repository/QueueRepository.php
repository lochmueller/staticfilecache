<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Domain\Repository;

class QueueRepository extends AbstractRepository
{
    /**
     * Find the entries for the worker.
     *
     * @todo move methods to iterator?
     */
    public function findOpen($limit = 999): array
    {
        $queryBuilder = $this->createQuery();

        return (array) $queryBuilder->select('*')
            ->from($this->getTableName())
            ->where($queryBuilder->expr()->eq('call_date', 0))
            ->setMaxResults($limit)
            ->orderBy('cache_priority', 'desc')
            ->executeQuery()
            ->fetchAllAssociative()
        ;
    }

    /**
     * Find open by identifier.
     *
     * @todo move methods to iterator?
     */
    public function countOpenByIdentifier($identifier): int
    {
        $queryBuilder = $this->createQuery();
        $where = $queryBuilder->expr()->and(
            $queryBuilder->expr()->eq('cache_url', $queryBuilder->createNamedParameter($identifier)),
            $queryBuilder->expr()->eq('call_date', 0)
        );

        return (int) $queryBuilder->select('uid')
            ->from($this->getTableName())
            ->where($where)
            ->executeQuery()
            ->rowCount()
        ;
    }

    /**
     * Find old entries.
     * @return list<int>
     *
     * @todo move methods to iterator?
     */
    public function findOldUids(): array
    {
        $queryBuilder = $this->createQuery();

        return $queryBuilder->select('uid')
            ->from($this->getTableName())
            ->where($queryBuilder->expr()->gt('call_date', 0))
            ->executeQuery()
            ->fetchFirstColumn()
        ;
    }

    protected function getTableName(): string
    {
        return 'tx_staticfilecache_queue';
    }
}

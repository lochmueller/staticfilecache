<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Domain\Repository;

class QueueRepository extends AbstractRepository
{
    /**
     * Find the entries for the worker.
     * @return iterable<array>
     */
    public function findOpen($limit = 999): iterable
    {
        $queryBuilder = $this->createQuery();

        yield from $queryBuilder->select('*')
            ->from($this->getTableName())
            ->where($queryBuilder->expr()->eq('call_date', 0))
            ->setMaxResults($limit)
            ->orderBy('cache_priority', 'desc')
            ->executeQuery()
            ->iterateAssociative()
        ;
    }

    /**
     * Find open by identifier.
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
     * @return iterable<array{uid: int}>
     */
    public function findOldUids(): iterable
    {
        $queryBuilder = $this->createQuery();

        yield from $queryBuilder->select('uid')
            ->from($this->getTableName())
            ->where($queryBuilder->expr()->gt('call_date', 0))
            ->executeQuery()
            ->iterateAssociative()
        ;
    }

    protected function getTableName(): string
    {
        return 'tx_staticfilecache_queue';
    }
}

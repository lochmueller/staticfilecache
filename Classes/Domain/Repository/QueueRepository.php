<?php

/**
 * QueueRepository.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Domain\Repository;

/**
 * QueueRepository.
 */
class QueueRepository extends AbstractRepository
{
    /**
     * Find the entries for the worker.
     *
     * @param int $limit
     *
     * @return array
     */
    public function findOpen($limit = 999): array
    {
        $queryBuilder = $this->createQuery();

        return (array)$queryBuilder->select('*')
            ->from($this->getTableName())
            ->where($queryBuilder->expr()->eq('call_date', $queryBuilder->createNamedParameter(0)))
            ->setMaxResults($limit)
            ->orderBy('cache_priority', 'desc')
            ->execute()
            ->fetchAll();
    }

    /**
     * Find open by identnfier.
     *
     * @param string $identifier
     *
     * @return int
     */
    public function countOpenByIdentifier($identifier): int
    {
        $queryBuilder = $this->createQuery();
        $where = $queryBuilder->expr()->andX(
            $queryBuilder->expr()->eq('cache_url', $queryBuilder->createNamedParameter($identifier)),
            $queryBuilder->expr()->eq('call_date', $queryBuilder->createNamedParameter(0))
        );

        return (int)$queryBuilder->select('uid')
            ->from($this->getTableName())
            ->where($where)
            ->execute()
            ->rowCount();
    }

    /**
     * Find old entries.
     *
     * @return array
     */
    public function findOld(): array
    {
        $queryBuilder = $this->createQuery();

        return (array)$queryBuilder->select('uid')
            ->from($this->getTableName())
            ->where($queryBuilder->expr()->gt('call_date', $queryBuilder->createNamedParameter(0)))
            ->execute()
            ->fetchAll();
    }

    /**
     * Get the table name.
     *
     * @return string
     */
    protected function getTableName(): string
    {
        return 'tx_staticfilecache_queue';
    }
}

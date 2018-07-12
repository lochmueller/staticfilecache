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
    public function findForWorker($limit = 999)
    {
        $queryBuilder = $this->createQuery();

        return (array)$queryBuilder->select('*')
            ->from($this->getTableName())
            ->where($queryBuilder->expr()->eq('call_date', $queryBuilder->createNamedParameter(0)))
            ->setMaxResults($limit)
            ->execute()
            ->fetchAll();
    }

    /**
     * Get the table name.
     *
     * @return string
     */
    protected function getTableName()
    {
        return 'tx_staticfilecache_queue';
    }
}

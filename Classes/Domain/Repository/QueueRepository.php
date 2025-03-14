<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Domain\Repository;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Exception;

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
     * @throws Exception
     */
    public function findOpenBatch(int $limit = 100, int $offset = 0): array
    {
        $queryBuilder = $this->createQuery();

        return $queryBuilder->select('*')
            ->from($this->getTableName())
            ->where($queryBuilder->expr()->eq('call_date', 0))
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->orderBy('cache_priority', 'desc')
            ->executeQuery()
            ->fetchAllAssociative();
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
     *
     * @return iterable<array{uid: int}>
     * @throws Exception
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

    /**
     * @throws Exception
     */
    public function findOldBatch(int $limit = 1000): array
    {
        $queryBuilder = $this->createQuery();

        return $queryBuilder->select('uid')
            ->from($this->getTableName())
            ->where($queryBuilder->expr()->gt('call_date', 0))
            ->setMaxResults($limit)
            ->executeQuery()
            ->fetchAllAssociative();
    }

    /**
     * Find existing identifiers (already in queue)
     *
     * @throws Exception
     */
    public function findExistingIdentifiers(array $identifiers): array
    {
        if (empty($identifiers)) {
            return [];
        }

        $queryBuilder = $this->createQuery();
        $result = $queryBuilder->select('cache_url')
            ->from($this->getTableName())
            ->where(
                $queryBuilder->expr()->and(
                    $queryBuilder->expr()->in(
                        'cache_url',
                        $queryBuilder->createNamedParameter($identifiers, ArrayParameterType::STRING)
                    ),
                    $queryBuilder->expr()->eq('call_date', 0)
                )
            )
            ->executeQuery()
            ->fetchAllAssociative();

        return array_column($result, 'cache_url');
    }

    /**
     * Delete entries in batch
     * @param array $uids List of UIDs to delete
     * @return int Number of deleted records
     */
    public function bulkDelete(array $uids): int
    {
        if (empty($uids)) {
            return 0;
        }

        $queryBuilder = $this->createQuery();
        return $queryBuilder->delete($this->getTableName())
            ->where(
                $queryBuilder->expr()->in(
                    'uid',
                    $queryBuilder->createNamedParameter($uids, ArrayParameterType::INTEGER)
                )
            )
            ->executeStatement();
    }

    /**
     * Insert multiple records at once with optimized transaction handling
     * @param array $records Array of record data to insert
     * @return int Number of inserted records
     * @throws Exception
     */
    public function bulkInsert(array $records): int
    {
        if (empty($records)) {
            return 0;
        }

        $connection = $this->getConnection();
        return $connection->bulkInsert(
            $this->getTableName(),
            $records,
            ['cache_url', 'page_uid', 'invalid_date', 'call_result', 'cache_priority']
        );
    }

    /**
     * @param \Generator $recordsGenerator Generator that yields batches of records
     * @param int $batchSize Recommended batch size (500-1000 for best performance)
     * @return int Total number of inserted records
     * @throws Exception
     * @throws \Throwable
     */
    public function streamedBulkInsert(\Generator $recordsGenerator, int $batchSize = 1000): int
    {
        $connection = $this->getConnection();
        $totalInserted = 0;
        $recordBatch = [];

        $connection->beginTransaction();
        try {
            foreach ($recordsGenerator as $record) {
                $recordBatch[] = $record;

                if (count($recordBatch) >= $batchSize) {
                    $inserted = $connection->bulkInsert(
                        $this->getTableName(),
                        $recordBatch,
                        ['cache_url', 'page_uid', 'invalid_date', 'call_result', 'cache_priority']
                    );
                    $totalInserted += $inserted;
                    $recordBatch = [];
                }
            }

            if (!empty($recordBatch)) {
                $inserted = $connection->bulkInsert(
                    $this->getTableName(),
                    $recordBatch,
                    ['cache_url', 'page_uid', 'invalid_date', 'call_result', 'cache_priority']
                );
                $totalInserted += $inserted;
            }

            $connection->commit();
        } catch (\Throwable $e) {
            $connection->rollBack();
            throw $e;
        }

        return $totalInserted;
    }

    /**
     * @throws Exception
     * @throws \Throwable
     */
    public function bulkUpdate(array $records): int
    {
        if (empty($records)) {
            return 0;
        }

        $connection = $this->getConnection();
        $count = 0;

        $connection->beginTransaction();
        try {
            foreach ($records as $record) {
                if (!isset($record['uid'])) {
                    continue;
                }

                $count += $connection->update(
                    $this->getTableName(),
                    [
                        'call_date' => $record['call_date'],
                        'call_result' => $record['call_result'],
                    ],
                    ['uid' => $record['uid']]
                );
            }

            $connection->commit();
        } catch (\Throwable $e) {
            $connection->rollBack();
            throw $e;
        }

        return $count;
    }

    /**
     * @throws Exception
     */
    public function countOpen(): int
    {
        $queryBuilder = $this->createQuery();
        return (int) $queryBuilder->count('uid')
            ->from($this->getTableName())
            ->where($queryBuilder->expr()->eq('call_date', 0))
            ->executeQuery()
            ->fetchOne();
    }

    protected function getTableName(): string
    {
        return 'tx_staticfilecache_queue';
    }
}

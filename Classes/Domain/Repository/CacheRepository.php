<?php

/**
 * CacheRepository.
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Domain\Repository;

use SFC\Staticfilecache\Service\ConfigurationService;
use SFC\Staticfilecache\Service\DateTimeService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * CacheRepository.
 */
class CacheRepository extends AbstractRepository
{
    /**
     * Get the expired cache identifiers.
     */
    public function findExpiredIdentifiers(): array
    {
        $queryBuilder = $this->createQuery();
        $rows = $queryBuilder->select('identifier')
            ->from($this->getTableName())
            ->where($queryBuilder->expr()->lt(
                'expires',
                $queryBuilder->createNamedParameter((new DateTimeService())->getCurrentTime(), \PDO::PARAM_INT)
            ))
            ->groupBy('identifier')
            ->execute()
            ->fetchAll()
        ;

        $cacheIdentifiers = [];
        foreach ($rows as $row) {
            $cacheIdentifiers[] = $row['identifier'];
        }

        return $cacheIdentifiers;
    }

    /**
     * Get all the cache identifiers.
     */
    public function findAllIdentifiers(): array
    {
        $queryBuilder = $this->createQuery();
        $rows = $queryBuilder->select('identifier')
            ->from($this->getTableName())
            ->groupBy('identifier')
            ->execute()
            ->fetchAll()
        ;

        $cacheIdentifiers = [];
        foreach ($rows as $row) {
            $cacheIdentifiers[] = $row['identifier'];
        }

        return $cacheIdentifiers;
    }

    /**
     * Get the table name.
     */
    protected function getTableName(): string
    {
        $prefix = 'cache_';
        $configuration = GeneralUtility::makeInstance(ConfigurationService::class);
        if ($configuration->isBool('renameTablesToOtherPrefix')) {
            $prefix = 'sfc_';
        }

        return $prefix.'staticfilecache';
    }
}

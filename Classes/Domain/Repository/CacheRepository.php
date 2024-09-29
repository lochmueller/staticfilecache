<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Domain\Repository;

use SFC\Staticfilecache\Service\ConfigurationService;
use SFC\Staticfilecache\Service\DateTimeService;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * CacheRepository.
 */
class CacheRepository extends AbstractRepository
{
    /**
     * Get the expired cache identifiers.
     * @todo move methods to iterator?
     */
    public function findExpiredIdentifiers(): array
    {
        $queryBuilder = $this->createQuery();
        $cacheIdentifiers = $queryBuilder->select('identifier')
            ->from($this->getTableName())
            ->where($queryBuilder->expr()->lt(
                'expires',
                $queryBuilder->createNamedParameter((new DateTimeService())->getCurrentTime(), Connection::PARAM_INT)
            ))
            ->groupBy('identifier')
            ->executeQuery()
            ->fetchFirstColumn()
        ;
        return $cacheIdentifiers;
    }

    /**
     * Get all the cache identifiers.
     * @todo move methods to iterator?
     */
    public function findAllIdentifiers(): array
    {
        $queryBuilder = $this->createQuery();
        $cacheIdentifiers = $queryBuilder->select('identifier')
            ->from($this->getTableName())
            ->groupBy('identifier')
            ->executeQuery()
            ->fetchFirstColumn()
        ;
        return $cacheIdentifiers;
    }


    protected function getTableName(): string
    {
        $prefix = 'cache_';
        $configuration = GeneralUtility::makeInstance(ConfigurationService::class);
        if ($configuration->isBool('renameTablesToOtherPrefix')) {
            $prefix = 'sfc_';
        }

        return $prefix . 'staticfilecache';
    }
}

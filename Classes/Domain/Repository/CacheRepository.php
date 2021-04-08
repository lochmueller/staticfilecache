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
     *
     * @param bool $hashedIdentifier
     * @return array<string>
     */
    public function findAllIdentifiers(bool $hashedIdentifier): array
    {
        $queryBuilder = $this->createQuery();
        $rows = $queryBuilder->select('*')
            ->from($this->getTableName())
            ->groupBy('identifier')
            ->execute()
            ->fetchAll()
        ;

        $cacheIdentifiers = [];
        foreach ($rows as $row) {
            if ($hashedIdentifier) {
                $content = unserialize($row['content'], ['allowed_classes' => false]);
                $url = $content['url'] ?? '';
                if (!$url) {
                    var_dump("no url");
                    continue;
                }
            } else {
                $url = $row['identifier'];
            }

            $cacheIdentifiers[] = $url;
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

<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Domain\Repository;

use Doctrine\DBAL\Exception;
use SFC\Staticfilecache\Service\ConfigurationService;
use SFC\Staticfilecache\Service\DateTimeService;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class CacheRepository extends AbstractRepository
{
    /**
     * Get the expired cache identifiers.
     *
     * @throws Exception
     */
    public function findExpiredIdentifiers(): array
    {
        $queryBuilder = $this->createQuery();

        return $queryBuilder->select('identifier')
            ->from($this->getTableName())
            ->where($queryBuilder->expr()->lt(
                'expires',
                $queryBuilder->createNamedParameter((new DateTimeService())->getCurrentTime(), Connection::PARAM_INT)
            ))
            ->groupBy('identifier')
            ->executeQuery()
            ->fetchFirstColumn();
    }

    /**
     * Count all identifiers in cache table
     *
     * @return int Total count of unique identifiers
     * @throws Exception
     */
    public function countAllIdentifiers(): int
    {
        $queryBuilder = $this->createQuery();

        return (int) $queryBuilder->selectLiteral('COUNT(DISTINCT ' . $queryBuilder->quoteIdentifier('identifier') . ')')
            ->from($this->getTableName())
            ->executeQuery()
            ->fetchOne();
    }

    /**
     * @return \Generator Yields identifiers one by one
     * @throws Exception
     */
    public function yieldAllIdentifiers(): \Generator
    {
        $queryBuilder = $this->createQuery();
        $result = $queryBuilder->select('identifier')
            ->from($this->getTableName())
            ->groupBy('identifier')
            ->executeQuery();

        while ($row = $result->fetchAssociative()) {
            yield $row['identifier'];
        }
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

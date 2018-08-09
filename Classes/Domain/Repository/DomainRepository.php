<?php

/**
 * DomainRepository.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Domain\Repository;

/**
 * DomainRepository.
 */
class DomainRepository extends AbstractRepository
{
    /**
     * Find one record by domainName.
     *
     * @param string $domainName
     *
     * @return array
     */
    public function findOneByDomainName(string $domainName): array
    {
        $queryBuilder = $this->createQuery();

        return (array)$queryBuilder->select('*')
            ->from($this->getTableName())
            ->where($queryBuilder->expr()->eq(
                'domainName',
                $queryBuilder->createNamedParameter($domainName, \PDO::PARAM_STR)
            ))
            ->execute()
            ->fetch();
    }

    /**
     * Get the table name.
     *
     * @return string
     */
    protected function getTableName(): string
    {
        return 'sys_domain';
    }
}

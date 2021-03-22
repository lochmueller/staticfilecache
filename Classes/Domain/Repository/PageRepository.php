<?php

/**
 * PageRepository.
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Domain\Repository;

/**
 * PageRepository.
 */
class PageRepository extends AbstractRepository
{
    /**
     * Get for backend output.
     *
     * @param int    $pageId
     * @param string $displayMode
     */
    public function findForBackend($pageId, $displayMode): array
    {
        $queryBuilder = $this->createQuery();

        $where = [];

        switch ($displayMode) {
            case 'current':
                $where[] = $queryBuilder->expr()->eq('uid', $pageId);

                break;

            case 'childs':
                $where[] = $queryBuilder->expr()->eq('pid', $pageId);

                break;

            case 'both':
            default:
                $where[] = $queryBuilder->expr()->eq('uid', $pageId);
                $where[] = $queryBuilder->expr()->eq('pid', $pageId);

                break;
        }

        return (array) $queryBuilder->select('*')
            ->from('pages')
            ->orWhere(...$where)
            ->execute()
            ->fetchAll()
        ;
    }

    /**
     * Get the table name.
     */
    protected function getTableName(): string
    {
        return 'pages';
    }
}

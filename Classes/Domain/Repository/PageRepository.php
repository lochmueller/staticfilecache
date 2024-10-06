<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Domain\Repository;

class PageRepository extends AbstractRepository
{
    /**
     * Get for backend output.
     *
     * @param int    $pageId
     * @param string $displayMode
     * @return iterable<array>
     */
    public function findForBackend($pageId, $displayMode): iterable
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

        yield from $queryBuilder->select('*')
            ->from('pages')
            ->orWhere(...$where)
            ->executeQuery()
            ->iterateAssociative()
        ;
    }

    protected function getTableName(): string
    {
        return 'pages';
    }
}

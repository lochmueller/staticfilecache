<?php
/**
 * Static file cache info module.
 */
declare(strict_types=1);

namespace SFC\Staticfilecache\Module;

use SFC\Staticfilecache\Service\CacheService;
use SFC\Staticfilecache\Service\ConfigurationService;
use TYPO3\CMS\Backend\Module\AbstractFunctionModule;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Static file cache info module.
 */
class CacheModule extends AbstractFunctionModule
{
    /**
     * MAIN function for static publishing information.
     *
     * @return string output HTML for the module
     */
    public function main()
    {
        $this->handleActions();
        $pageId = (int) $this->pObj->id;

        /** @var StandaloneView $renderer */
        $renderer = GeneralUtility::makeInstance(StandaloneView::class);
        $moduleTemplate = 'EXT:staticfilecache/Resources/Private/Templates/Module.html';
        $renderer->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName($moduleTemplate));
        $renderer->assignMultiple([
            'requestUri' => GeneralUtility::getIndpEnv('REQUEST_URI'),
            'rows' => $this->getCachePagesEntries($pageId),
            'pageId' => $pageId,
            'backendDisplayMode' => $this->getDisplayMode(),
        ]);

        return $renderer->render();
    }

    /**
     * Get cache pages entries.
     *
     * @param int    $pageId
     *
     * @return array
     */
    protected function getCachePagesEntries(int $pageId): array
    {
        $rows = [];
        $cache = GeneralUtility::makeInstance(CacheService::class)->getCache();

        $dbRows = $this->getDatabaseRows();

        foreach ($dbRows as $row) {
            $cacheEntries = $cache->getByTag('sfc_pageId_' . $row['uid']);
            foreach ($cacheEntries as $identifier => $info) {
                $rows[] = [
                    'uid' => $row['uid'],
                    'title' => BackendUtility::getRecordTitle(
                        'pages',
                        $row,
                        true
                    ),
                    'identifier' => $identifier,
                    'info' => $info,
                ];
            }
        }

        return $rows;
    }

    /**
     * Get the DB rows
     *
     * @return array
     */
    protected function getDatabaseRows():array{
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder = $connectionPool->getQueryBuilderForTable('pages');

        $where = [];
        switch ($this->getDisplayMode()) {
            case 'current':
                $where[] = $queryBuilder->expr()->eq('uid', $pageId);
                break;
            case 'childs':
                $where[] = $queryBuilder->expr()->eq('pid', $pageId);
                break;
            case 'both':
                $where[] = $queryBuilder->expr()->eq('uid', $pageId);
                $where[] = $queryBuilder->expr()->eq('pid', $pageId);
                break;
        }

        return $queryBuilder->select('*')
            ->from('pages')
            ->orWhere(...$where)
            ->execute()
            ->fetchAll();
    }

    /**
     * Get display mode
     * 
     * @return string
     */
    protected function getDisplayMode() : string {
        $configurationService = GeneralUtility::makeInstance(ConfigurationService::class);
        return $configurationService->getBackendDisplayMode();
    }

    /**
     * Handles incoming actions (e.g. removing all expired pages).
     */
    protected function handleActions()
    {
        $action = GeneralUtility::_GP('ACTION');

        if (isset($action['removeExpiredPages']) && (bool) $action['removeExpiredPages']) {
            GeneralUtility::makeInstance(CacheService::class)->getCache()->collectGarbage();
        }
    }
}

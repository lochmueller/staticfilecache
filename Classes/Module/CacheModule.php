<?php
/**
 * Static file cache info module
 *
 * @author  Tim Lochmüller
 * @author Michiel Roos
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Module;

use SFC\Staticfilecache\Service\CacheService;
use TYPO3\CMS\Backend\Module\AbstractFunctionModule;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Static file cache info module
 */
class CacheModule extends AbstractFunctionModule
{

    /**
     * Page ID
     *
     * @var integer
     */
    protected $pageId = 0;

    /**
     * MAIN function for static publishing information
     *
     * @return    string        Output HTML for the module.
     */
    public function main()
    {
        $this->handleActions();
        $this->pageId = (int)$this->pObj->id;

        /** @var StandaloneView $renderer */
        $renderer = GeneralUtility::makeInstance(StandaloneView::class);
        $moduleTemplate = 'EXT:staticfilecache/Resources/Private/Templates/Module.html';
        $renderer->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName($moduleTemplate));
        $renderer->assignMultiple([
            'requestUri' => GeneralUtility::getIndpEnv('REQUEST_URI'),
            'rows' => $this->getCachePagesEntries(),
            'pageId' => $this->pageId
        ]);

        return $renderer->render();
    }

    /**
     * Get cache pages entries
     *
     * @return array
     */
    protected function getCachePagesEntries(): array
    {
        $rows = [];
        $cache = GeneralUtility::makeInstance(CacheService::class)->getCache();


        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder = $connectionPool->getQueryBuilderForTable('pages');
        $dbRows = $queryBuilder->select('*')
            ->from('pages')
            ->where($queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($this->pageId)))
            ->execute()
            ->fetchAll();

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
     * Handles incoming actions (e.g. removing all expired pages).
     */
    protected function handleActions()
    {
        $action = GeneralUtility::_GP('ACTION');

        if (isset($action['removeExpiredPages']) && (bool)$action['removeExpiredPages']) {
            GeneralUtility::makeInstance(CacheService::class)->getCache()->collectGarbage();
        }
    }
}

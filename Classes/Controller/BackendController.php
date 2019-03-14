<?php

/**
 * Static file cache info module.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Controller;

use SFC\Staticfilecache\Domain\Repository\PageRepository;
use SFC\Staticfilecache\Service\CacheService;
use SFC\Staticfilecache\Service\ConfigurationService;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Static file cache backend module.
 */
class BackendController extends ActionController
{
    /**
     * MAIN function for static publishing information.
     */
    public function listAction()
    {
        $this->view->assignMultiple([
            'rows' => $this->getCachePagesEntries(),
            'pageId' => $this->getCurrentUid(),
            'backendDisplayMode' => $this->getDisplayMode(),
        ]);
    }

    /**
     * Handles incoming actions (e.g. removing all expired pages).
     *
     * @throws \TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     */
    public function removeExpiredPagesAction()
    {
        GeneralUtility::makeInstance(CacheService::class)->get()->collectGarbage();
        $flashMessage = new FlashMessage('Garbe collection is done', 'Garbe collection', FlashMessage::OK, true);
        $flashMessagesQueue = $this->controllerContext->getFlashMessageQueue();
        $flashMessagesQueue->addMessage($flashMessage);
        $this->redirect('list');
    }

    /**
     * Get cache pages entries.
     *
     * @return array
     */
    protected function getCachePagesEntries(): array
    {
        $rows = [];
        try {
            $cache = GeneralUtility::makeInstance(CacheService::class)->get();
        } catch (\Exception $exception) {
            $logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
            $logger->error('Problems by fetching the cache: ' . $exception->getMessage() . ' / ' . $exception->getFile() . ':' . $exception->getLine());

            return $rows;
        }

        $dbRows = GeneralUtility::makeInstance(PageRepository::class)->findForBackend($this->getCurrentUid(), $this->getDisplayMode());

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
     * Get display mode.
     *
     * @return string
     */
    protected function getDisplayMode(): string
    {
        $configurationService = GeneralUtility::makeInstance(ConfigurationService::class);

        return $configurationService->getBackendDisplayMode();
    }

    /**
     * Get the current UID.
     *
     * @return int
     */
    protected function getCurrentUid(): int
    {
        return (int)GeneralUtility::_GET('id');
    }
}

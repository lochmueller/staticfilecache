<?php

/**
 * StaticFileCache backend module.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Controller;

use SFC\Staticfilecache\Domain\Repository\PageRepository;
use SFC\Staticfilecache\Domain\Repository\QueueRepository;
use SFC\Staticfilecache\Service\CacheService;
use SFC\Staticfilecache\Service\ConfigurationService;
use SFC\Staticfilecache\Service\QueueService;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * StaticFileCache backend module.
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
     * Boost action.
     *
     * @param bool $run
     */
    public function boostAction($run = false)
    {
        $configurationService = $this->objectManager->get(ConfigurationService::class);
        $queueRepository = $this->objectManager->get(QueueRepository::class);
        if ($run) {
            $items = $queueRepository->findOpen(10);
            $queueService = GeneralUtility::makeInstance(QueueService::class);
            try {
                foreach ($items as $item) {
                    $queueService->runSingleRequest($item);
                }
            } catch (\Exception $ex) {
            }

            $this->addFlashMessage('Run ' . \count($items) . ' entries', 'Runner', FlashMessage::OK, true);
        }
        $this->view->assignMultiple([
            'enable' => (bool)$configurationService->get('boostMode'),
            'open' => \count($queueRepository->findOpen(99999999)),
            'old' => \count($queueRepository->findOld()),
        ]);
    }

    public function supportAction()
    {
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

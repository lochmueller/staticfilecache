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
use SFC\Staticfilecache\Service\HtaccessConfigurationService;
use SFC\Staticfilecache\Service\QueueService;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
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
     *
     * @param string $filter
     */
    public function listAction(string $filter = ''): void
    {
        $filter = $this->setFilter($filter);
        $this->view->assignMultiple([
            'rows' => $this->getCachePagesEntries($filter),
            'filter' => $filter,
            'pageId' => $this->getCurrentUid(),
            'backendDisplayMode' => $this->getDisplayMode(),
        ]);
    }

    /**
     * Boost action.
     *
     * @param bool $run
     */
    public function boostAction($run = false): void
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
            } catch (\Exception $exception) {
                $this->addFlashMessage('Error in run: ' . $exception->getMessage(), 'Runner', FlashMessage::ERROR, true);
            }

            $this->addFlashMessage('Run ' . \count($items) . ' entries', 'Runner', FlashMessage::OK, true);
        }
        $this->view->assignMultiple([
            'enable' => (bool)$configurationService->get('boostMode'),
            'open' => \count($queueRepository->findOpen(99999999)),
            'old' => \count($queueRepository->findOld()),
        ]);
    }

    /**
     * Support action.
     */
    public function supportAction(): void
    {
        $htaccessConfigurationService = GeneralUtility::makeInstance(HtaccessConfigurationService::class);
        $this->view->assignMultiple([
            'foundHtaccess' => $htaccessConfigurationService->foundConfigurationInHtaccess(),
            'missingModules' => $htaccessConfigurationService->getMissingApacheModules(),
        ]);
    }

    /**
     * Set filter
     *
     * @param string $filter
     *
     * @return string
     */
    protected function setFilter(string $filter): string
    {
        $user = $this->getBackendUser();
        $validFilter = ['all', 'cached', 'notCached'];
        if ('' === $filter) {
            $filter = (string)$user->getSessionData('sfc_filter');
        }
        if (!\in_array($filter, $validFilter, true)) {
            $filter = 'all';
        } else {
            $user->setAndSaveSessionData('sfc_filter', $filter);
        }

        return $filter;
    }

    /**
     * Get backend user
     *
     * @return BackendUserAuthentication
     */
    protected function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }

    /**
     * Get cache pages entries.
     *
     * @param string $filter
     * @return array
     */
    protected function getCachePagesEntries(string $filter): array
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
                    'cached' => !is_array($info['explanation']) || empty($info['explanation']),
                    'identifier' => $identifier,
                    'info' => $info,
                ];
            }
        }

        return array_filter($rows, function ($row) use ($filter) {
            if ($filter === 'all') {
                return true;
            }
            return ('cached' === $filter && $row['cached']) || ('notCached' === $filter && !$row['cached']);
        });
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

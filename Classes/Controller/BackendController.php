<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Controller;

use TYPO3\CMS\Core\Messaging\AbstractMessage;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SFC\Staticfilecache\Domain\Repository\PageRepository;
use SFC\Staticfilecache\Domain\Repository\QueueRepository;
use SFC\Staticfilecache\Service\CacheService;
use SFC\Staticfilecache\Service\ConfigurationService;
use SFC\Staticfilecache\Service\EnvironmentService;
use SFC\Staticfilecache\Service\HtaccessConfigurationService;
use SFC\Staticfilecache\Service\QueueService;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * StaticFileCache backend module.
 */
class BackendController extends ActionController
{
    protected QueueService $queueService;
    protected ModuleTemplateFactory $moduleTemplateFactory;

    /**
     * BackendController constructor.
     */
    public function __construct(QueueService $queueService, ModuleTemplateFactory $moduleTemplateFactory)
    {
        $this->queueService = $queueService;
        $this->moduleTemplateFactory = $moduleTemplateFactory;
    }

    public function listAction(ServerRequestInterface $request, string $filter = ''): ResponseInterface
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($request);

        $filter = $this->setFilter($filter);
        $this->view->assignMultiple([
            'rows' => $this->getCachePagesEntries($filter),
            'filter' => $filter,
            'pageId' => $this->getCurrentUid(),
            'backendDisplayMode' => $this->getDisplayMode(),
        ]);

        $moduleTemplate->setContent($this->view->render());
        return new HtmlResponse($moduleTemplate->renderContent());
    }

    public function boostAction(ServerRequestInterface $request, $run = false): ResponseInterface
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($request);

        $configurationService = GeneralUtility::makeInstance(ConfigurationService::class);
        $queueRepository = GeneralUtility::makeInstance(QueueRepository::class);
        if ($run) {
            $items = $queueRepository->findOpen(10);

            try {
                foreach ($items as $item) {
                    $this->queueService->runSingleRequest($item);
                }
            } catch (\Exception $exception) {
                $this->addFlashMessage('Error in run: '.$exception->getMessage(), 'Runner', AbstractMessage::ERROR, true);
            }

            $this->addFlashMessage('Run '.\count($items).' entries', 'Runner', AbstractMessage::OK, true);
        }
        $this->view->assignMultiple([
            'enable' => (bool) $configurationService->get('boostMode'),
            'open' => \count($queueRepository->findOpen(99999999)),
            'old' => \count($queueRepository->findOld()),
        ]);

        $moduleTemplate->setContent($this->view->render());
        return new HtmlResponse($moduleTemplate->renderContent());
    }

    public function supportAction(ServerRequestInterface $request): ResponseInterface
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($request);

        $htaccessConfigurationService = GeneralUtility::makeInstance(HtaccessConfigurationService::class);
        $environmentService = GeneralUtility::makeInstance(EnvironmentService::class);
        $this->view->assignMultiple([
            'foundHtaccess' => $htaccessConfigurationService->foundConfigurationInHtaccess(),
            'htaccessPaths' => $htaccessConfigurationService->getHtaccessPaths(),
            'missingModules' => $htaccessConfigurationService->getMissingApacheModules(),
            'useCrawler' => ExtensionManagementUtility::isLoaded('crawler'),
            'envInfoLink' => $environmentService->getLink(),
            'envInfoMarkdown' => $environmentService->getMarkdown(),
        ]);

        $moduleTemplate->setContent($this->view->render());
        return new HtmlResponse($moduleTemplate->renderContent());
    }

    /**
     * Set filter.
     */
    protected function setFilter(string $filter): string
    {
        $user = $this->getBackendUser();
        $validFilter = ['all', 'cached', 'notCached'];
        if ('' === $filter) {
            $filter = (string) $user->getSessionData('sfc_filter');
        }
        if (!\in_array($filter, $validFilter, true)) {
            $filter = 'all';
        } else {
            $user->setAndSaveSessionData('sfc_filter', $filter);
        }

        return $filter;
    }

    /**
     * Get backend user.
     */
    protected function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }

    /**
     * Get cache pages entries.
     */
    protected function getCachePagesEntries(string $filter): array
    {
        $rows = [];

        try {
            $cache = GeneralUtility::makeInstance(CacheService::class)->get();
        } catch (\Exception $exception) {
            $logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
            $logger->error('Problems by fetching the cache: '.$exception->getMessage().' / '.$exception->getFile().':'.$exception->getLine());

            return $rows;
        }

        $dbRows = GeneralUtility::makeInstance(PageRepository::class)->findForBackend($this->getCurrentUid(), $this->getDisplayMode());

        foreach ($dbRows as $row) {
            $cacheEntries = $cache->getByTag('pageId_'.$row['uid']);
            foreach ($cacheEntries as $identifier => $info) {
                $explanation = $info['explanation'] ?? [];
                $rows[] = [
                    'uid' => $row['uid'],
                    'title' => BackendUtility::getRecordTitle(
                        'pages',
                        $row,
                        true
                    ),
                    'cached' => !\is_array($explanation) || empty($explanation),
                    'identifier' => $identifier,
                    'info' => $info,
                ];
            }
        }

        return array_filter($rows, function ($row) use ($filter) {
            if ('all' === $filter) {
                return true;
            }

            return ('cached' === $filter && $row['cached']) || ('notCached' === $filter && !$row['cached']);
        });
    }

    /**
     * Get display mode.
     */
    protected function getDisplayMode(): string
    {
        $configurationService = GeneralUtility::makeInstance(ConfigurationService::class);

        return $configurationService->getBackendDisplayMode();
    }

    /**
     * Get the current UID.
     */
    protected function getCurrentUid(): int
    {
        return (int) GeneralUtility::_GET('id');
    }
}

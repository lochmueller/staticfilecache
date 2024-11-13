<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Controller;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use SFC\Staticfilecache\Cache\UriFrontend;
use TYPO3\CMS\Backend\Template\Components\Menu\Menu;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use Psr\Http\Message\ResponseInterface;
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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class BackendController extends ActionController implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        readonly protected QueueService          $queueService,
        readonly protected ModuleTemplateFactory $moduleTemplateFactory,
        readonly protected ConfigurationService  $configurationService,
        readonly protected CacheService          $cacheService,
    ) {}

    public function listAction(string $filter = ''): ResponseInterface
    {
        $filter = $this->setFilter($filter);
        $viewVariables = [
            'rows' => $this->getCachePagesEntries($filter),
            'filter' => $filter,
            'pageId' => $this->getCurrentUid(),
            'backendDisplayMode' => $this->getDisplayMode(),
        ];

        return $this->createModuleTemplate()
            ->assignMultiple($viewVariables)
            ->renderResponse('Backend/List');
    }

    public function boostAction(bool $run = false): ResponseInterface
    {
        $queueRepository = GeneralUtility::makeInstance(QueueRepository::class);
        if ($run) {
            $items = $queueRepository->findOpen(10);

            try {
                foreach ($items as $item) {
                    $this->queueService->runSingleRequest($item);
                }
            } catch (\Exception $exception) {
                $this->addFlashMessage('Error in run: ' . $exception->getMessage(), 'Runner', ContextualFeedbackSeverity::ERROR, true);
            }

            $this->addFlashMessage('Run ' . \count($items) . ' entries', 'Runner', ContextualFeedbackSeverity::OK, true);
        }
        $viewVariables = [
            'enable' => (bool) $this->configurationService->get('boostMode'),
            'open' => \count(iterator_to_array($queueRepository->findOpen(99999999))),
            'old' => \count(iterator_to_array($queueRepository->findOldUids())),
        ];

        return $this->createModuleTemplate()
            ->assignMultiple($viewVariables)
            ->renderResponse('Backend/Boost');
    }

    public function supportAction(): ResponseInterface
    {
        $htaccessConfigurationService = GeneralUtility::makeInstance(HtaccessConfigurationService::class);
        $environmentService = GeneralUtility::makeInstance(EnvironmentService::class);
        $viewVariables = [
            'foundHtaccess' => $htaccessConfigurationService->foundConfigurationInHtaccess(),
            'htaccessPaths' => $htaccessConfigurationService->getHtaccessPaths(),
            'missingModules' => $htaccessConfigurationService->getMissingApacheModules(),
            'envInfoLink' => $environmentService->getLink(),
            'envInfoMarkdown' => $environmentService->getMarkdown(),
        ];

        return $this->createModuleTemplate()
            ->assignMultiple($viewVariables)
            ->renderResponse('Backend/Support');
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

    protected function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }

    protected function getCachePagesEntries(string $filter): array
    {
        $rows = [];

        try {
            /** @var UriFrontend $cache */
            $cache = $this->cacheService->get();
        } catch (\Exception $exception) {
            $this->logger->error('Problems by fetching the cache: ' . $exception->getMessage() . ' / ' . $exception->getFile() . ':' . $exception->getLine());

            return $rows;
        }

        $dbRows = GeneralUtility::makeInstance(PageRepository::class)->findForBackend($this->getCurrentUid(), $this->getDisplayMode());

        foreach ($dbRows as $row) {
            $cacheEntries = $cache->getByTag('pageId_' . $row['uid']);
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

    protected function getDisplayMode(): string
    {
        return $this->configurationService->getBackendDisplayMode();
    }

    protected function getCurrentUid(): int
    {
        return (int) ($this->request->getQueryParams()['id'] ?? 0);
    }

    protected function createModuleTemplate(): ModuleTemplate
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request)
            ->setFlashMessageQueue($this->getFlashMessageQueue())
            ->setModuleClass('tx-staticfilecache')
            ->setTitle('StaticFileCache');

        $menuItems = [
            'list' => 'List (Overview)',
            'boost' => 'Boostmode',
            'support' => 'Configuration, Support, Documentation...',
        ];

        $menu = new Menu();
        $menu->setIdentifier('func');
        foreach ($menuItems as $action => $label) {
            $menuItem = $menu->makeMenuItem()
                ->setTitle($label)
                ->setHref($this->uriBuilder->uriFor($action))
                ->setActive($this->request->getControllerActionName() === $action);

            $menu->addMenuItem($menuItem);
        }

        $moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->addMenu($menu);

        return $moduleTemplate;
    }
}

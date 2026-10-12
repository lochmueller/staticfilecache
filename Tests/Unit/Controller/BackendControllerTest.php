<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Controller;

use Psr\Log\LoggerInterface;
use SFC\Staticfilecache\Cache\UriFrontend;
use SFC\Staticfilecache\Domain\Repository\PageRepository;
use SFC\Staticfilecache\Service\CacheService;
use SFC\Staticfilecache\Service\QueueService;
use SFC\Staticfilecache\Tests\Unit\AbstractTest;
use SFC\Staticfilecache\Tests\Unit\Fixtures\TestBackendController;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException;
use TYPO3\CMS\Core\Schema\TcaSchemaFactory;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;

/**
 * BackendControllerTest.
 *
 * @internal
 * @coversNothing
 */
final class BackendControllerTest extends AbstractTest
{
    private string $backendDisplayMode = 'current';

    private array $queryParams = [];

    public function testSetFilterUseSessionDataAsFallback(): void
    {
        $backendUser = $this->createMock(BackendUserAuthentication::class);
        $backendUser->expects(self::once())->method('getSessionData')->with('sfc_filter')->willReturn('cached');
        $backendUser->expects(self::once())->method('setAndSaveSessionData')->with('sfc_filter', 'cached');
        $GLOBALS['BE_USER'] = $backendUser;

        self::assertSame('cached', $this->getController()->callSetFilter(''));
    }

    public function testSetFilterStoreValidFilter(): void
    {
        $backendUser = $this->createMock(BackendUserAuthentication::class);
        $backendUser->expects(self::never())->method('getSessionData');
        $backendUser->expects(self::once())->method('setAndSaveSessionData')->with('sfc_filter', 'notCached');
        $GLOBALS['BE_USER'] = $backendUser;

        self::assertSame('notCached', $this->getController()->callSetFilter('notCached'));
    }

    public function testSetFilterFallbackToAllForInvalidFilter(): void
    {
        $backendUser = $this->createMock(BackendUserAuthentication::class);
        $backendUser->expects(self::never())->method('setAndSaveSessionData');
        $GLOBALS['BE_USER'] = $backendUser;

        self::assertSame('all', $this->getController()->callSetFilter('invalid'));
    }

    public function testDisplayModeAndCurrentUid(): void
    {
        $this->backendDisplayMode = 'childs';
        $this->queryParams = ['id' => '42'];
        $controller = $this->getController();

        self::assertSame('childs', $controller->callGetDisplayMode());
        self::assertSame(42, $controller->callGetCurrentUid());
    }

    public function testGetCachePagesEntriesLogErrorIfCacheIsMissing(): void
    {
        $cacheService = $this->createStub(CacheService::class);
        $cacheService->method('get')->willThrowException(new NoSuchCacheException('Missing cache', 1));
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error')->with(self::stringStartsWith('Problems by fetching the cache: Missing cache'));
        $controller = $this->getController($cacheService);
        $controller->setLogger($logger);

        self::assertSame([], $controller->callGetCachePagesEntries('all'));
    }

    public static function filterProvider(): iterable
    {
        yield 'all' => ['all', ['https://example.com/cached', 'https://example.com/not-cached']];
        yield 'cached' => ['cached', ['https://example.com/cached']];
        yield 'not cached' => ['notCached', ['https://example.com/not-cached']];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('filterProvider')]
    public function testGetCachePagesEntriesFilterEntries(string $filter, array $expectedIdentifiers): void
    {
        $this->backendDisplayMode = 'both';
        $this->queryParams = ['id' => '1'];

        $pageRepository = $this->createMock(PageRepository::class);
        $pageRepository->expects(self::once())->method('findForBackend')->with(1, 'both')->willReturn([['uid' => 1, 'pid' => 0, 'title' => 'Home']]);
        $tcaSchemaFactory = $this->createStub(TcaSchemaFactory::class);
        $tcaSchemaFactory->method('has')->willReturn(false);
        $this->registerContainerServices([
            PageRepository::class => $pageRepository,
            TcaSchemaFactory::class => $tcaSchemaFactory,
        ]);

        $cache = $this->createMock(UriFrontend::class);
        $cache->expects(self::once())->method('getByTag')->with('pageId_1')->willReturn([
            'https://example.com/cached' => ['explanation' => []],
            'https://example.com/not-cached' => ['explanation' => ['NoUserOrGroupSet' => 'User is logged in']],
        ]);
        $cacheService = $this->createStub(CacheService::class);
        $cacheService->method('get')->willReturn($cache);

        $controller = $this->getController($cacheService);
        $rows = $controller->callGetCachePagesEntries($filter);

        self::assertSame($expectedIdentifiers, array_column($rows, 'identifier'));
        foreach ($rows as $row) {
            self::assertSame(1, $row['uid']);
            self::assertSame('https://example.com/cached' === $row['identifier'], $row['cached']);
        }
    }

    private function getController(?CacheService $cacheService = null): TestBackendController
    {
        $configurationService = $this->setExtensionConfiguration(['backendDisplayMode' => $this->backendDisplayMode]);
        $moduleTemplateFactory = (new \ReflectionClass(ModuleTemplateFactory::class))->newInstanceWithoutConstructor();
        $controller = new TestBackendController($this->createStub(QueueService::class), $moduleTemplateFactory, $configurationService, $cacheService ?? $this->createStub(CacheService::class));

        $request = $this->createStub(RequestInterface::class);
        $request->method('getQueryParams')->willReturn($this->queryParams);
        $controller->setTestRequest($request);

        return $controller;
    }
}

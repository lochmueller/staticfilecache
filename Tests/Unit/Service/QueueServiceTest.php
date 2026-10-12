<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Service;

use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\NullLogger;
use SFC\Staticfilecache\Domain\Repository\QueueRepository;
use SFC\Staticfilecache\Service\CacheService;
use SFC\Staticfilecache\Service\ClientService;
use SFC\Staticfilecache\Service\ConfigurationService;
use SFC\Staticfilecache\Service\QueueService;
use SFC\Staticfilecache\Tests\Unit\AbstractTest;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;

/**
 * QueueServiceTest.
 *
 * @internal
 * @coversNothing
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
final class QueueServiceTest extends AbstractTest
{
    protected QueueRepository&MockObject $queueRepository;

    protected ClientService&MockObject $clientService;

    protected FrontendInterface&MockObject $cache;

    protected ConfigurationService $configurationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->queueRepository = $this->createMock(QueueRepository::class);
        $this->clientService = $this->createMock(ClientService::class);
        $this->cache = $this->createMock(FrontendInterface::class);
        $this->configurationService = $this->setExtensionConfiguration(['boostMode' => '1']);
    }

    public function testAddIdentifiersIgnoreEmptyList(): void
    {
        $this->queueRepository->expects(self::never())->method('findExistingIdentifiers');
        $this->queueRepository->expects(self::never())->method('bulkInsert');

        $this->getQueueService()->addIdentifiers([]);
    }

    public function testAddIdentifiersSkipExistingIdentifiers(): void
    {
        $this->queueRepository->expects(self::once())->method('findExistingIdentifiers')->willReturn(['https://example.com/']);
        $this->queueRepository->expects(self::never())->method('bulkInsert');

        $this->getQueueService()->addIdentifiers(['https://example.com/']);
    }

    public function testAddIdentifiersInsertNewIdentifiersWithPriority(): void
    {
        $this->queueRepository->method('findExistingIdentifiers')->willReturn(['https://example.com/existing']);
        $this->cache->method('get')->willReturnMap([
            ['https://example.com/a', ['priority' => 42]],
            ['https://example.com/b', false],
        ]);

        $records = null;
        $this->queueRepository->expects(self::once())->method('bulkInsert')->willReturnCallback(static function (array $data) use (&$records): int {
            $records = $data;

            return \count($data);
        });

        $this->getQueueService()->addIdentifiers(['https://example.com/a', 'https://example.com/existing', 'https://example.com/b']);

        self::assertSame(['https://example.com/a', 'https://example.com/b'], array_column($records, 'cache_url'));
        self::assertSame([42, QueueService::PRIORITY_LOW], array_column($records, 'cache_priority'));
        self::assertSame([0, 0], array_column($records, 'page_uid'));
    }

    public function testAddIdentifiersInsertInBatches(): void
    {
        $identifiers = array_map(static fn(int $i): string => 'https://example.com/' . $i, range(1, QueueService::BATCH_SIZE + 1));
        $this->queueRepository->method('findExistingIdentifiers')->willReturn([]);
        $this->queueRepository->expects(self::exactly(2))->method('bulkInsert')->willReturn(1);

        $this->getQueueService()->addIdentifiers($identifiers, QueueService::PRIORITY_HIGH);
    }

    public function testAddIdentifierSkipAlreadyQueuedIdentifier(): void
    {
        $this->queueRepository->expects(self::once())->method('countOpenByIdentifier')->with('https://example.com/')->willReturn(1);
        $this->queueRepository->expects(self::never())->method('insert');

        $this->getQueueService()->addIdentifier('https://example.com/');
    }

    public function testAddIdentifierUseOverridePriority(): void
    {
        $this->queueRepository->method('countOpenByIdentifier')->willReturn(0);
        $this->cache->expects(self::never())->method('get');
        $this->queueRepository->expects(self::once())
            ->method('insert')
            ->with(self::callback(static fn(array $data): bool => 'https://example.com/' === $data['cache_url']
                && QueueService::PRIORITY_MEDIUM === $data['cache_priority']
                && '' === $data['call_result']));

        $this->getQueueService()->addIdentifier('https://example.com/', QueueService::PRIORITY_MEDIUM);
    }

    public function testAddIdentifierIgnoreCacheErrorsOnPriorityLookup(): void
    {
        $this->queueRepository->method('countOpenByIdentifier')->willReturn(0);
        $this->cache->method('get')->willThrowException(new \RuntimeException('Cache broken'));
        $this->queueRepository->expects(self::once())
            ->method('insert')
            ->with(self::callback(static fn(array $data): bool => QueueService::PRIORITY_LOW === $data['cache_priority']));

        $this->getQueueService()->addIdentifier('https://example.com/');
    }

    public function testRunBatchRequestsIgnoreEmptyList(): void
    {
        $this->clientService->expects(self::never())->method('runMultipleRequests');

        self::assertSame([], $this->getQueueService()->runBatchRequests([]));
    }

    public function testRunBatchRequests(): void
    {
        $entries = [
            ['uid' => 1, 'cache_url' => 'https://example.com/a', 'page_uid' => 10],
            ['uid' => 2, 'cache_url' => 'https://example.com/b', 'page_uid' => 20],
            ['uid' => 3, 'cache_url' => 'https://example.com/c', 'page_uid' => 0],
        ];

        $this->cache->method('has')->willReturnMap([
            ['https://example.com/a', true],
            ['https://example.com/b', false],
            ['https://example.com/c', true],
        ]);
        $this->cache->expects(self::exactly(2))->method('remove');
        // Only the failed page with a page UID is flushed
        $this->cache->expects(self::once())->method('flushByTag')->with('pageId_20');

        $this->clientService->expects(self::exactly(2))
            ->method('runMultipleRequests')
            ->willReturnCallback(function (array $urls): array {
                self::assertSame('0', $this->configurationService->get('boostMode'), 'Boost mode is disabled while running');

                return 2 === \count($urls) ? [200, 500] : [404];
            });

        $updates = [];
        $this->queueRepository->expects(self::exactly(2))->method('bulkUpdate')->willReturnCallback(static function (array $records) use (&$updates): int {
            $updates = array_merge($updates, $records);

            return \count($records);
        });

        $result = $this->getQueueService()->runBatchRequests($entries, 2);

        self::assertSame([200, 500, 404], $result);
        self::assertSame([1, 2, 3], array_column($updates, 'uid'));
        self::assertSame([200, 500, 404], array_column($updates, 'call_result'));
        self::assertSame('1', $this->configurationService->get('boostMode'), 'Boost mode override is reset');
    }

    public function testRunSingleRequestSuccessful(): void
    {
        $this->cache->method('has')->willReturn(true);
        $this->cache->expects(self::once())->method('remove')->with('https://example.com/');
        $this->cache->expects(self::never())->method('flushByTag');
        $this->clientService->expects(self::once())->method('runSingleRequest')->with('https://example.com/')->willReturn(200);
        $this->queueRepository->expects(self::once())
            ->method('update')
            ->with(self::callback(static fn(array $data): bool => 200 === $data['call_result']), ['uid' => 5]);

        $this->getQueueService()->runSingleRequest(['uid' => '5', 'cache_url' => 'https://example.com/', 'page_uid' => 3]);

        self::assertSame('1', $this->configurationService->get('boostMode'));
    }

    public function testRunSingleRequestFlushPageOnError(): void
    {
        $this->cache->method('has')->willReturn(false);
        $this->cache->expects(self::never())->method('remove');
        $this->cache->expects(self::once())->method('flushByTag')->with('pageId_3');
        $this->clientService->method('runSingleRequest')->willReturn(900);
        $this->queueRepository->expects(self::once())
            ->method('update')
            ->with(self::callback(static fn(array $data): bool => 900 === $data['call_result']), ['uid' => 5]);

        $this->getQueueService()->runSingleRequest(['uid' => 5, 'cache_url' => 'https://example.com/', 'page_uid' => 3]);
    }

    protected function getQueueService(): QueueService
    {
        $cacheService = $this->createStub(CacheService::class);
        $cacheService->method('get')->willReturn($this->cache);

        $queueService = new QueueService($this->queueRepository, $this->configurationService, $this->clientService, $cacheService);
        $queueService->setLogger(new NullLogger());

        return $queueService;
    }
}

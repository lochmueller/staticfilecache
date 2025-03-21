<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Service;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException;
use SFC\Staticfilecache\Command\BoostQueueCommand;
use SFC\Staticfilecache\Domain\Repository\QueueRepository;

/**
 * @see BoostQueueCommand
 */
class QueueService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public const PRIORITY_HIGH = 2000;
    public const PRIORITY_MEDIUM = 1000;
    public const PRIORITY_LOW = 0;
    public const BATCH_SIZE = 500;

    public function __construct(
        protected QueueRepository $queueRepository,
        protected ConfigurationService $configurationService,
        protected ClientService $clientService,
        protected CacheService $cacheService
    ) {}

    public function addIdentifiers(array $identifiers, int $overridePriority = self::PRIORITY_LOW): void
    {
        if (empty($identifiers)) {
            return;
        }

        $existingIdentifiers = $this->queueRepository->findExistingIdentifiers($identifiers);
        $filteredIdentifiers = array_diff($identifiers, $existingIdentifiers);

        if (empty($filteredIdentifiers)) {
            return;
        }

        $this->logger->debug('SFC Queue adding batch', ['count' => count($filteredIdentifiers)]);

        $batches = array_chunk($filteredIdentifiers, self::BATCH_SIZE);
        foreach ($batches as $batch) {
            $insertData = [];
            foreach ($batch as $identifier) {
                $priority = $this->determinePriority($identifier, $overridePriority);
                $insertData[] = [
                    'cache_url' => $identifier,
                    'page_uid' => 0,
                    'invalid_date' => time(),
                    'call_result' => '',
                    'cache_priority' => $priority,
                ];
            }
            $this->queueRepository->bulkInsert($insertData);
        }
    }

    public function addIdentifier(string $identifier, int $overridePriority = self::PRIORITY_LOW): void
    {
        $count = $this->queueRepository->countOpenByIdentifier($identifier);
        if ($count > 0) {
            return;
        }

        $this->logger->debug('SFC Queue add', [$identifier]);

        $priority = $this->determinePriority($identifier, $overridePriority);

        $data = [
            'cache_url' => $identifier,
            'page_uid' => 0,
            'invalid_date' => time(),
            'call_result' => '',
            'cache_priority' => $priority,
        ];

        $this->queueRepository->insert($data);
    }

    protected function determinePriority(string $identifier, int $overridePriority): int
    {
        if ($overridePriority) {
            return $overridePriority;
        }

        $priority = self::PRIORITY_LOW;
        try {
            $cache = $this->cacheService->get();
            $infos = $cache->get($identifier);
            if (isset($infos['priority'])) {
                $priority = (int) $infos['priority'];
            }
        } catch (\Throwable) {
            // Ignore
        }

        return $priority;
    }

    /**
     * @throws NoSuchCacheException
     * @throws \Exception
     * @throws \Throwable
     */
    public function runBatchRequests(array $runEntries, int $maxParallel = 10): array
    {
        if (empty($runEntries)) {
            return [];
        }

        $this->configurationService->override('boostMode', '0');
        $cache = $this->cacheService->get();
        $statusResults = [];

        $chunks = array_chunk($runEntries, $maxParallel);
        foreach ($chunks as $chunk) {
            foreach ($chunk as $runEntry) {
                if ($cache->has($runEntry['cache_url'])) {
                    $cache->remove($runEntry['cache_url']);
                }
            }

            $this->logger->debug('SFC Queue batch run', ['count' => count($chunk)]);

            $statusCodes = $this->clientService->runMultipleRequests(
                array_column($chunk, 'cache_url')
            );

            $batchResults = [];
            foreach ($chunk as $index => $runEntry) {
                $statusCode = $statusCodes[$index] ?? 0;
                $statusResults[] = $statusCode;

                $batchResults[] = [
                    'uid' => (int) $runEntry['uid'],
                    'call_date' => time(),
                    'call_result' => $statusCode,
                    'page_uid' => $runEntry['page_uid'],
                ];

                if (200 !== $statusCode && $runEntry['page_uid'] > 0) {
                    // Call the flush if the page is not accessible
                    $cache->flushByTag('pageId_' . $runEntry['page_uid']);
                }
            }

            $this->queueRepository->bulkUpdate($batchResults);
        }

        $this->configurationService->reset('boostMode');
        return $statusResults;
    }

    /**
     * Run a single request with guzzle.
     *
     * @throws NoSuchCacheException
     */
    public function runSingleRequest(array $runEntry): void
    {
        $this->configurationService->override('boostMode', '0');
        $cache = $this->cacheService->get();

        if ($cache->has($runEntry['cache_url'])) {
            $cache->remove($runEntry['cache_url']);
        }

        $this->logger->debug('SFC Queue run', $runEntry);

        $statusCode = $this->clientService->runSingleRequest($runEntry['cache_url']);

        $data = [
            'call_date' => time(),
            'call_result' => $statusCode,
        ];

        if (200 !== $statusCode) {
            // Call the flush, if the page is not accessible
            $cache->flushByTag('pageId_' . $runEntry['page_uid']);
        }

        $this->queueRepository->update($data, ['uid' => (int) $runEntry['uid']]);
        $this->configurationService->reset('boostMode');
    }
}

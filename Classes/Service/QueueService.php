<?php

/**
 * Queue service.
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Service;

use SFC\Staticfilecache\Command\BoostQueueCommand;
use SFC\Staticfilecache\Domain\Repository\QueueRepository;

/**
 * Queue service.
 *
 * @see BoostQueueCommand
 */
class QueueService extends AbstractService
{
    public const PRIORITY_HIGH = 2000;
    public const PRIORITY_MEDIUM = 1000;
    public const PRIORITY_LOW = 0;

    /**
     * Queue repository.
     */
    protected QueueRepository $queueRepository;

    protected ConfigurationService $configurationService;

    protected ClientService $clientService;

    protected CacheService $cacheService;

    /**
     * QueueService constructor.
     */
    public function __construct(QueueRepository $queueRepository, ConfigurationService $configurationService, ClientService $clientService, CacheService $cacheService)
    {
        $this->queueRepository = $queueRepository;
        $this->configurationService = $configurationService;
        $this->clientService = $clientService;
        $this->cacheService = $cacheService;
    }

    /**
     * Add identifiers to Queue.
     */
    public function addIdentifiers(array $identifiers, int $overridePriority = self::PRIORITY_LOW): void
    {
        foreach ($identifiers as $identifier) {
            $this->addIdentifier($identifier, $overridePriority);
        }
    }

    /**
     * Add identifier to Queue.
     */
    public function addIdentifier(string $identifier, int $overridePriority = self::PRIORITY_LOW): void
    {
        $count = $this->queueRepository->countOpenByIdentifier($identifier);
        if ($count > 0) {
            return;
        }

        $this->logger->debug('SFC Queue add', [$identifier]);

        if ($overridePriority) {
            $priority = $overridePriority;
        } else {
            $priority = self::PRIORITY_LOW;

            try {
                $cache = $this->cacheService->get();
                $infos = $cache->get($identifier);
                if (isset($infos['priority'])) {
                    $priority = (int) $infos['priority'];
                }
            } catch (\Exception $exception) {
            }
        }

        $data = [
            'cache_url' => $identifier,
            'page_uid' => 0,
            'invalid_date' => time(),
            'call_result' => '',
            'cache_priority' => $priority,
        ];

        $this->queueRepository->insert($data);
    }

    /**
     * Run a single request with guzzle.
     *
     * @throws \TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException
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
            // Call the flush, if the page is not accessable
            $cache->flushByTag('pageId_'.$runEntry['page_uid']);
        }

        $this->queueRepository->update($data, ['uid' => (int) $runEntry['uid']]);
        $this->configurationService->reset('boostMode');
    }
}

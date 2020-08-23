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

    /**
     * Queue repository.
     *
     * @var QueueRepository
     */
    protected $queueRepository;

    /**
     * @var ConfigurationService
     */
    protected $configurationService;

    /**
     * @var ClientService
     */
    protected $clientService;

    /**
     * @var CacheService
     */
    protected $cacheService;

    /**
     * QueueService constructor.
     * @param QueueRepository $queueRepository
     * @param ConfigurationService $configurationService
     * @param ClientService $clientService
     * @param CacheService $cacheService
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
     *
     * @param array $identifiers
     */
    public function addIdentifiers(array $identifiers): void
    {
        foreach ($identifiers as $identifier) {
            $this->addIdentifier($identifier);
        }
    }

    /**
     * Add identifier to Queue.
     *
     * @param string $identifier
     */
    public function addIdentifier(string $identifier): void
    {
        $count = $this->queueRepository->countOpenByIdentifier($identifier);
        if ($count > 0) {
            return;
        }

        $this->logger->debug('SFC Queue add', [$identifier]);

        $priority = 0;
        try {
            $cache = $this->cacheService->get();
            $infos = $cache->get($identifier);
            if (isset($infos['priority'])) {
                $priority = (int)$infos['priority'];
            }
        } catch (\Exception $exception) {
        }

        $data = [
            'cache_url' => $identifier,
            'page_uid' => 0,
            'invalid_date' => \time(),
            'call_result' => '',
            'cache_priority' => $priority,
        ];

        $this->queueRepository->insert($data);
    }

    /**
     * Run a single request with guzzle.
     *
     * @param array $runEntry
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
            'call_date' => \time(),
            'call_result' => $statusCode,
        ];

        if (200 !== $statusCode) {
            // Call the flush, if the page is not accessable
            $cache->flushByTag('sfc_pageId_' . $runEntry['page_uid']);
        }

        $this->queueRepository->update($data, ['uid' => (int)$runEntry['uid']]);
    }
}

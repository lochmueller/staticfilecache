<?php

/**
 * Queue service.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Service;

use SFC\Staticfilecache\Command\BoostQueueCleanupCommand;
use SFC\Staticfilecache\Command\BoostQueueRunCommand;
use SFC\Staticfilecache\Domain\Repository\QueueRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Queue service.
 *
 * @see BoostQueueRunCommand
 * @see BoostQueueCleanupCommand
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
     * QueueService constructor.
     */
    public function __construct()
    {
        $this->queueRepository = GeneralUtility::makeInstance(QueueRepository::class);
    }

    /**
     * Add identifiers to Queue.
     *
     * @param array $identifiers
     */
    public function addIdentifiers(array $identifiers)
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
    public function addIdentifier(string $identifier)
    {
        $count = $this->queueRepository->countOpenByIdentifier($identifier);
        if ($count > 0) {
            return;
        }

        $data = [
            'cache_url' => $identifier,
            'page_uid' => 0,
            'invalid_date' => \time(),
            'call_result' => '',
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
    public function runSingleRequest(array $runEntry)
    {
        if (!\defined('SFC_QUEUE_WORKER')) {
            \define('SFC_QUEUE_WORKER', true);
        }

        $clientService = GeneralUtility::makeInstance(ClientService::class);
        $statusCode = $clientService->runSingleRequest($runEntry['cache_url']);

        $data = [
            'call_date' => \time(),
            'call_result' => $statusCode,
        ];

        if (200 !== $statusCode) {
            // Call the flush, if the page is not accessable
            $cache = GeneralUtility::makeInstance(CacheService::class)->get();
            $cache->flushByTag('sfc_pageId_' . $runEntry['page_uid']);
            if ($cache->has($runEntry['cache_url'])) {
                $cache->remove($runEntry['cache_url']);
            }
        }

        $this->queueRepository->update($data, ['uid' => (int)$runEntry['uid']]);
    }
}

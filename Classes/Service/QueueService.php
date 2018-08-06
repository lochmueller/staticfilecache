<?php

/**
 * Queue service.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Service;

use SFC\Staticfilecache\Domain\Repository\QueueRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Queue service.
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
     * Run the queue.
     *
     * @param int $limitItems
     */
    public function run(int $limitItems = 0)
    {
        \define('SFC_QUEUE_WORKER', true);

        $limit = $limitItems > 0 ? $limitItems : 999;
        $rows = $this->queueRepository->findOpen($limit);

        foreach ($rows as $runEntry) {
            $this->runSingleRequest($runEntry);
        }
    }

    /**
     * Cleanup the cache queue.
     */
    public function cleanup()
    {
        $rows = $this->queueRepository->findOld();
        foreach ($rows as $row) {
            $this->queueRepository->delete(['uid' => $row['uid']]);
        }
    }

    /**
     * Add identifiert to Queue.
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
    protected function runSingleRequest(array $runEntry)
    {
        $clientService = GeneralUtility::makeInstance(ClientService::class);
        $statusCode = $clientService->runSingleRequest($runEntry['cache_url']);

        $data = [
            'call_date' => \time(),
            'call_result' => $statusCode,
        ];

        if (200 !== $statusCode) {
            // Call the flush, if the page is not accessable
            $cache = GeneralUtility::makeInstance(CacheService::class)->getCache();
            $cache->flushByTag('sfc_pageId_' . $runEntry['page_uid']);
            if ($cache->has($runEntry['cache_url'])) {
                $cache->remove($runEntry['cache_url']);
            }
        }

        $this->queueRepository->update($data, ['uid' => (int)$runEntry['uid']]);
    }
}

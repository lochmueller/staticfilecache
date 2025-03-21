<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Command;

use Doctrine\DBAL\Exception;
use SFC\Staticfilecache\Domain\Repository\QueueRepository;
use SFC\Staticfilecache\Service\QueueService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException;

class BoostQueueCommand extends AbstractCommand
{
    protected const DEFAULT_BATCH_SIZE = 50;
    protected const DEFAULT_CLEANUP_BATCH_SIZE = 1000;
    protected const DEFAULT_CONCURRENCY = 10;

    public function __construct(protected QueueRepository $queueRepository, protected QueueService $queueService)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();
        $this->addOption('limit-items', null, InputOption::VALUE_REQUIRED, 'Limit the items that are crawled. 0 => all', 500)
            ->addOption('stop-processing-after', null, InputOption::VALUE_REQUIRED, 'Stop crawling new items after N seconds since scheduler task started. 0 => infinite', 240)
            ->addOption('avoid-cleanup', null, InputOption::VALUE_NONE, 'Avoid the cleanup of the queue items')
            ->addOption('batch-size', null, InputOption::VALUE_REQUIRED, 'Number of items to process in a single batch', self::DEFAULT_BATCH_SIZE)
            ->addOption('concurrency', null, InputOption::VALUE_REQUIRED, 'Number of concurrent requests to run within a batch', self::DEFAULT_CONCURRENCY)
        ;
    }

    /**
     * @throws NoSuchCacheException
     * @throws Exception
     * @throws \Throwable
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $startTime = time();
        $stopProcessingAfter = (int) $input->getOption('stop-processing-after');
        $totalLimit = (int) $input->getOption('limit-items');
        $totalLimit = $totalLimit > 0 ? $totalLimit : 5000;

        $batchSize = (int) $input->getOption('batch-size');
        $batchSize = $batchSize > 0 ? $batchSize : self::DEFAULT_BATCH_SIZE;
        $batchSize = min($batchSize, $totalLimit);

        $concurrency = (int) $input->getOption('concurrency');
        $concurrency = $concurrency > 0 ? $concurrency : self::DEFAULT_CONCURRENCY;

        $queueCount = $this->queueRepository->countOpen();
        $io->writeln(sprintf(
            'Found %d items in queue. Will process up to %d items with batch size %d.',
            $queueCount,
            $totalLimit,
            $batchSize
        ));

        $processedCount = 0;
        $successCount = 0;
        $failedCount = 0;
        $offset = 0;

        while ($processedCount < $totalLimit) {
            if ($stopProcessingAfter > 0 && time() >= $startTime + $stopProcessingAfter) {
                $io->note(sprintf('Stopping after %d seconds as requested.', $stopProcessingAfter));
                break;
            }

            $entriesBatch = $this->queueRepository->findOpenBatch($batchSize, $offset);

            if (empty($entriesBatch)) {
                $io->note('No more items in queue.');
                break;
            }

            $statusResults = $this->queueService->runBatchRequests($entriesBatch, $concurrency);

            $batchSuccess = count(array_filter($statusResults, fn($status) => $status === 200));
            $successCount += $batchSuccess;
            $failedCount += (count($entriesBatch) - $batchSuccess);

            $processedCount += count($entriesBatch);

            $io->writeln(sprintf(
                'Processed batch of %d items (%d total, %d successful, %d failed).',
                count($entriesBatch),
                $processedCount,
                $successCount,
                $failedCount
            ));

            if (count($entriesBatch) < $batchSize) {
                break;
            }

            $offset += $batchSize;
        }

        $duration = time() - $startTime;
        $rate = $duration > 0 ? round($processedCount / $duration, 2) : $processedCount;

        $io->success(sprintf(
            '%d items processed in %d seconds (%.2f items/sec). Success: %d, Failed: %d',
            $processedCount,
            $duration,
            $rate,
            $successCount,
            $failedCount
        ));

        if (!(bool) $input->getOption('avoid-cleanup')) {
            $this->cleanupQueue($io);
        }

        return self::SUCCESS;
    }

    /**
     * @throws Exception
     */
    protected function cleanupQueue(SymfonyStyle $io): void
    {
        $startTime = microtime(true);
        $totalDeleted = 0;
        $batchSize = self::DEFAULT_CLEANUP_BATCH_SIZE;

        $io->writeln('Starting batch cleanup of old queue entries...');

        while (true) {
            $batch = $this->queueRepository->findOldBatch($batchSize);
            if (empty($batch)) {
                break;
            }

            $uids = array_column($batch, 'uid');
            $count = $this->queueRepository->bulkDelete($uids);
            $totalDeleted += $count;

            if (count($batch) < $batchSize) {
                break;
            }
        }

        $duration = round(microtime(true) - $startTime, 2);
        $io->success(sprintf('%d items removed in %s seconds.', $totalDeleted, $duration));
    }
}

<?php

/**
 * BoostQueueRunCommand.
 */
declare(strict_types = 1);

namespace SFC\Staticfilecache\Command;

use SFC\Staticfilecache\Domain\Repository\QueueRepository;
use SFC\Staticfilecache\Service\QueueService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * BoostQueueRunCommand.
 */
class BoostQueueCommand extends AbstractCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        parent::configure();
        $this->setDescription('Run (work on) the cache boost queue.');

        $this->addArgument('limit-items', InputArgument::OPTIONAL, 'Limit the items that are crawled. 0 => all', 0);
        $this->addArgument('stop-processing-after', InputArgument::OPTIONAL, 'Stop crawling new items after N seconds since scheduler task started. 0 => infinite', 0);
        $this->addArgument('avoid-cleanup', InputArgument::OPTIONAL, 'Avoid the cleanup of the queue items', 0);
    }

    /**
     * Executes the current command.
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null null or 0 if everything went fine, or an error code
     *
     * @throws \TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException
     *
     * @see setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $queueRepository = GeneralUtility::makeInstance(QueueRepository::class);
        $queueService = GeneralUtility::makeInstance(QueueService::class);
        $io = new SymfonyStyle($input, $output);

        $startTime = \time();
        $stopProcessingAfter = (int)$input->getArgument('stop-processing-after');
        $limit = (int)$input->getArgument('limit-items');
        $limit = $limit > 0 ? $limit : 5000;
        $rows = $queueRepository->findOpen($limit);

        $io->progressStart(\count($rows));
        foreach ($rows as $runEntry) {
            if ($stopProcessingAfter > 0 && \time() >= $startTime + $stopProcessingAfter) {
                $io->note('Skip after "stopProcessingAfter" time.');
                break;
            }

            $queueService->runSingleRequest($runEntry);
            $io->progressAdvance();
        }
        $io->progressFinish();

        $io->success(\count($rows) . ' items are done (perhaps not all are processed).');

        if ((int)$input->getArgument('avoid-cleanup') !== 0) {
            $this->cleanupQueue($io);
        }

        return 0;
    }

    /**
     * Cleanup queue
     *
     * @param SymfonyStyle $io
     */
    protected function cleanupQueue(SymfonyStyle $io)
    {
        $queueRepository = GeneralUtility::makeInstance(QueueRepository::class);

        $rows = $queueRepository->findOld();
        $io->progressStart(\count($rows));
        foreach ($rows as $row) {
            $queueRepository->delete(['uid' => $row['uid']]);
            $io->progressAdvance();
        }
        $io->progressFinish();

        $io->success(\count($rows) . ' items are removed.');
    }
}

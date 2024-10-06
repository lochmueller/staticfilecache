<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Command;

use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException;
use SFC\Staticfilecache\Domain\Repository\QueueRepository;
use SFC\Staticfilecache\Service\QueueService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class BoostQueueCommand extends AbstractCommand
{
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
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $startTime = time();
        $stopProcessingAfter = (int) $input->getOption('stop-processing-after');
        $limit = (int) $input->getOption('limit-items');
        $limit = $limit > 0 ? $limit : 5000;

        $count = 0;
        foreach ($this->queueRepository->findOpen($limit) as $runEntry) {
            if ($stopProcessingAfter > 0 && time() >= $startTime + $stopProcessingAfter) {
                $io->note('Skip after "stopProcessingAfter" time.');

                break;
            }

            $this->queueService->runSingleRequest($runEntry);
            $count++;
        }

        $io->success($count . ' items are done (perhaps not all are processed).');

        if (!(bool) $input->getOption('avoid-cleanup')) {
            $this->cleanupQueue($io);
        }

        return self::SUCCESS;
    }


    protected function cleanupQueue(SymfonyStyle $io): void
    {
        $count = 0;
        foreach ($this->queueRepository->findOldUids() as $row) {
            $this->queueRepository->delete(['uid' => $row['uid']]);
            $count++;
        }
        $io->success($count . ' items are removed.');
    }
}

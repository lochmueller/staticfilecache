<?php

/**
 * BoostQueueRunCommand.
 */
declare(strict_types=1);

namespace SFC\Staticfilecache\Command;

use SFC\Staticfilecache\Domain\Repository\QueueRepository;
use SFC\Staticfilecache\Service\QueueService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * BoostQueueRunCommand.
 */
class BoostQueueCommand extends AbstractCommand
{
    /**
     * @var QueueRepository
     */
    protected $queueRepository;

    /**
     * @var QueueService
     */
    protected $queueService;

    public function __construct(QueueRepository $queueRepository, QueueService $queueService)
    {
        $this->queueRepository = $queueRepository;
        $this->queueService = $queueService;
        parent::__construct();
    }

    /**
     * Configures the current command.
     */
    protected function configure(): void
    {
        parent::configure();
        // @todo When compatibility is set to TYPO3 v11+ only, the description can be removed as it is defined in Services.yaml
        $this->setDescription('Run (work on) the cache boost queue. Call this task every 5 minutes.')
            ->addOption('limit-items', null, InputOption::VALUE_REQUIRED, 'Limit the items that are crawled. 0 => all', 500)
            ->addOption('stop-processing-after', null, InputOption::VALUE_REQUIRED, 'Stop crawling new items after N seconds since scheduler task started. 0 => infinite', 240)
            ->addOption('avoid-cleanup', null, InputOption::VALUE_NONE, 'Avoid the cleanup of the queue items')
        ;
    }

    /**
     * Executes the current command.
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method.
     *
     * @throws \TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException
     *
     * @return null|int null or 0 if everything went fine, or an error code
     *
     * @see setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $startTime = time();
        $stopProcessingAfter = (int) $input->getOption('stop-processing-after');
        $limit = (int) $input->getOption('limit-items');
        $limit = $limit > 0 ? $limit : 5000;
        $rows = $this->queueRepository->findOpen($limit);

        $io->progressStart(\count($rows));
        foreach ($rows as $runEntry) {
            if ($stopProcessingAfter > 0 && time() >= $startTime + $stopProcessingAfter) {
                $io->note('Skip after "stopProcessingAfter" time.');

                break;
            }

            $this->queueService->runSingleRequest($runEntry);
            $io->progressAdvance();
        }
        $io->progressFinish();

        $io->success(\count($rows).' items are done (perhaps not all are processed).');

        if (!(bool) $input->getOption('avoid-cleanup')) {
            $this->cleanupQueue($io);
        }

        return 0;
    }

    /**
     * Cleanup queue.
     */
    protected function cleanupQueue(SymfonyStyle $io): void
    {
        $rows = $this->queueRepository->findOld();
        $io->progressStart(\count($rows));
        foreach ($rows as $row) {
            $this->queueRepository->delete(['uid' => $row['uid']]);
            $io->progressAdvance();
        }
        $io->progressFinish();

        $io->success(\count($rows).' items are removed.');
    }
}

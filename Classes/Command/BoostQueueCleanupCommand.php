<?php

/**
 * BoostQueueCleanupCommand.
 */
declare(strict_types = 1);

namespace SFC\Staticfilecache\Command;

use SFC\Staticfilecache\Domain\Repository\QueueRepository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * BoostQueueCleanupCommand.
 */
class BoostQueueCleanupCommand extends AbstractCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        parent::configure();
        $this->setDescription('Cleanup the cache boost queue entries.');
    }

    /**
     * Executes the current command.
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null null or 0 if everything went fine, or an error code
     *
     * @see setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $queueRepository = GeneralUtility::makeInstance(QueueRepository::class);
        $io = new SymfonyStyle($input, $output);
        $rows = $queueRepository->findOld();
        $io->progressStart(\count($rows));
        foreach ($rows as $row) {
            $queueRepository->delete(['uid' => $row['uid']]);
            $io->progressAdvance();
        }
        $io->progressFinish();

        $io->success(\count($rows) . ' items are removed.');

        return 0;
    }
}

<?php

/**
 * BoostQueueRunCommand.
 */
declare(strict_types = 1);

namespace SFC\Staticfilecache\Command;

use SFC\Staticfilecache\Service\QueueService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * BoostQueueRunCommand.
 */
class BoostQueueRunCommand extends Command
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setDescription('Run (work on) the cache boost queue.');

        $this->addArgument('limit-items', InputArgument::OPTIONAL, 'Limit the items that are crawled. 0 => all', 0);
        $this->addArgument('stop-processing-after', InputArgument::OPTIONAL, 'Stop crawling new items after N seconds since scheduler task started. 0 => infinite', 0);
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
     * @return int|null null or 0 if everything went fine, or an error code
     *
     * @see setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $queue = GeneralUtility::makeInstance(QueueService::class);
        $queue->run((int)$input->getArgument('limit-items'), (int)$input->getArgument('stop-processing-after'));

        return 0;
    }
}

<?php

declare(strict_types = 1);
/**
 * BoostQueueCleanupCommand.
 */

namespace SFC\Staticfilecache\Command;

use SFC\Staticfilecache\Service\QueueService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * BoostQueueCleanupCommand.
 */
class BoostQueueCleanupCommand extends Command
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
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
     * @throws \TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException
     *
     * @return int|null null or 0 if everything went fine, or an error code
     *
     * @see setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $queue = GeneralUtility::makeInstance(QueueService::class);
        $queue->cleanup();

        return 0;
    }
}

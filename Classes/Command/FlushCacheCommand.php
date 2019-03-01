<?php

/**
 * FlushCacheCommand.
 */
declare(strict_types = 1);

namespace SFC\Staticfilecache\Command;

use SFC\Staticfilecache\Service\CacheService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * FlushCacheCommand.
 */
class FlushCacheCommand extends Command
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setDescription('Flush the cache. If the boost mode is active, all pages are recrawlt.');
        $this->addArgument('force-boost-mode-flush', InputArgument::OPTIONAL, 'Force a boost mode flush', false);
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
     * @throws \TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException
     * @throws \TYPO3\CMS\Core\Cache\Exception\NoSuchCacheGroupException
     *
     * @return int|null null or 0 if everything went fine, or an error code
     *
     * @see setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getArgument('force-boost-mode-flush')) {
            \define('SFC_QUEUE_WORKER', true);
        }
        $cacheService = GeneralUtility::makeInstance(CacheService::class);
        $cacheService->get()->flush();
        $cacheService->getManager()->flushCachesInGroup('pages');

        return 0;
    }
}

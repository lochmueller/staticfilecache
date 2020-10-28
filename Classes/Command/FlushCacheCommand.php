<?php

/**
 * FlushCacheCommand.
 */
declare(strict_types=1);

namespace SFC\Staticfilecache\Command;

use SFC\Staticfilecache\Service\CacheService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * FlushCacheCommand.
 */
class FlushCacheCommand extends AbstractCommand
{
    /**
     * Configures the current command.
     */
    protected function configure(): void
    {
        parent::configure();
        $this->setDescription('Flush the cache. If the boost mode is active, all pages are added to the queue (you have to run the BoostQueueRun Command to recrawl the pages). If you use the force-boost-mode-flush argument, you directly drop the cache even the page is in Boostmode.')
            ->addOption('force-boost-mode-flush', null, InputOption::VALUE_NONE, 'Force a boost mode flush')
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
     * @throws \TYPO3\CMS\Core\Cache\Exception\NoSuchCacheGroupException
     *
     * @return null|int null or 0 if everything went fine, or an error code
     *
     * @see setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cacheService = GeneralUtility::makeInstance(CacheService::class);
        $cacheService->flush((bool) $input->getOption('force-boost-mode-flush'));

        return 0;
    }
}

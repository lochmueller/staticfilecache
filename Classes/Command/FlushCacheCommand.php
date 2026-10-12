<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Command;

use SFC\Staticfilecache\Service\CacheService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheGroupException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

#[AsCommand(
    name: 'staticfilecache:flushCache',
    description: 'Flush the cache. If the boost mode is active, all pages are added to the queue (you have to run the BoostQueueRun Command to recrawl the pages). If you use the force-boost-mode-flush argument, you directly drop the cache even the page is in Boostmode.',
)]
class FlushCacheCommand extends AbstractCommand
{
    protected function configure(): void
    {
        parent::configure();
        $this->addOption('force-boost-mode-flush', null, InputOption::VALUE_NONE, 'Force a boost mode flush');
    }

    /**
     * @throws NoSuchCacheException
     * @throws NoSuchCacheGroupException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $cacheService = GeneralUtility::makeInstance(CacheService::class);
        $cacheService->flush((bool) $input->getOption('force-boost-mode-flush'));

        return self::SUCCESS;
    }
}

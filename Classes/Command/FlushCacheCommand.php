<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Command;

use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheGroupException;
use SFC\Staticfilecache\Service\CacheService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FlushCacheCommand extends AbstractCommand
{
    protected function configure(): void
    {
        parent::configure();
        $this->addOption('force-boost-mode-flush', null, InputOption::VALUE_NONE, 'Force a boost mode flush');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $cacheService = GeneralUtility::makeInstance(CacheService::class);
        $cacheService->flush((bool) $input->getOption('force-boost-mode-flush'));

        return self::SUCCESS;
    }
}

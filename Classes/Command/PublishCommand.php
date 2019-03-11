<?php

/**
 * PublishCommand.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Command;

use SFC\Staticfilecache\Service\PublishService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * PublishCommand.
 */
class PublishCommand extends AbstractCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setDescription('Run the PublishService incl. the Slots to publish the current StaticFileCache.');
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
        $publishService = GeneralUtility::makeInstance(PublishService::class);
        $publishService->publish();

        return 0;
    }
}

<?php
/**
 * PublishCommandController.
 */
declare(strict_types = 1);

namespace SFC\Staticfilecache\Command;

use SFC\Staticfilecache\Service\PublishService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * PublishCommandController.
 */
class PublishCommandController extends AbstractCommandController
{
    /**
     * Publish command.
     */
    public function publishCommand()
    {
        $publishService = GeneralUtility::makeInstance(PublishService::class);
        $publishService->publish();
    }
}

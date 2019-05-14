<?php

/**
 * PublishService.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Service;

use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

/**
 * PublishService.
 */
class PublishService extends AbstractService
{
    /**
     * Publish.
     */
    public function publish()
    {
        $arguments = [
            'cacheDirectory' => GeneralUtility::makeInstance(CacheService::class)->getRelativeBaseDirectory(),
        ];

        $objectManager = new ObjectManager();
        /** @var Dispatcher $dispatcher */
        $dispatcher = $objectManager->get(Dispatcher::class);
        try {
            $dispatcher->dispatch(__CLASS__, __METHOD__, $arguments);
        } catch (\Exception $exception) {
            $logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
            $logger->error('Problems in publis signal: ' . $exception->getMessage() . ' / ' . $exception->getFile() . ':' . $exception->getLine());
        }
    }
}

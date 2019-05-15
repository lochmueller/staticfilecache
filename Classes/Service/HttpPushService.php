<?php

/**
 * HttpPushService.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Service;

use SFC\Staticfilecache\Service\HttpPush\AbstractHttpPush;
use SFC\Staticfilecache\Service\HttpPush\FontHttpPush;
use SFC\Staticfilecache\Service\HttpPush\ImageHttpPush;
use SFC\Staticfilecache\Service\HttpPush\ScriptHttpPush;
use SFC\Staticfilecache\Service\HttpPush\StyleHttpPush;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

/**
 * HttpPushService.
 */
class HttpPushService extends AbstractService
{
    /**
     * Get http push headers.
     *
     * @param string $content
     *
     * @return array
     */
    public function getHttpPushHeaders(string $content): array
    {
        $headers = [];
        /** @var ConfigurationService $configurationService */
        $configurationService = GeneralUtility::makeInstance(ConfigurationService::class);
        if ($configurationService->isBool('sendHttp2PushEnable')) {
            $limit = (int)$configurationService->get('sendHttp2PushFileLimit');
            $extensions = GeneralUtility::trimExplode(',', (string)$configurationService->get('sendHttp2PushFileExtensions'), true);
            $handlers = $this->getHttpPushHandler();

            foreach ($extensions as $extension) {
                foreach ($handlers as $handler) {
                    /** @var AbstractHttpPush $handler */
                    if ($handler->canHandleExtension($extension)) {
                        $headers = \array_merge($headers, $handler->getHeaders($content));
                    }
                }
            }

            $headers = \array_slice($headers, 0, $limit);
        }

        return $headers;
    }

    /**
     * Get HTTP push handlers.
     *
     * @return array
     */
    protected function getHttpPushHandler(): array
    {
        $arguments = [
            'httpPushServices' => [
                StyleHttpPush::class,
                ScriptHttpPush::class,
                ImageHttpPush::class,
                FontHttpPush::class,
            ],
        ];

        $objectManager = new ObjectManager();
        /** @var Dispatcher $dispatcher */
        $dispatcher = $objectManager->get(Dispatcher::class);
        try {
            $dispatcher->dispatch(__CLASS__, 'getHttpPushHandler', $arguments);
        } catch (\Exception $exception) {
            $logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
            $logger->error('Problems in publis signal: ' . $exception->getMessage() . ' / ' . $exception->getFile() . ':' . $exception->getLine());
        }

        $objects = [];
        foreach ((array)$arguments['httpPushServices'] as $httpPushService) {
            $objects[] = GeneralUtility::makeInstance($httpPushService);
        }

        return $objects;
    }
}

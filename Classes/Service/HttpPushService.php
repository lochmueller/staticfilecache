<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Service;

use Psr\EventDispatcher\EventDispatcherInterface;
use SFC\Staticfilecache\Event\HttpPushHeaderEvent;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class HttpPushService
{
    public function __construct(protected readonly EventDispatcherInterface $eventDispatcher) {}

    /**
     * Get http push headers.
     */
    public function getHttpPushHeaders(string $content): array
    {
        $headers = [];

        /** @var ConfigurationService $configurationService */
        $configurationService = GeneralUtility::makeInstance(ConfigurationService::class);
        if ($configurationService->isBool('sendHttp2PushEnable')) {
            $limit = (int) $configurationService->get('sendHttp2PushFileLimit');
            $limitToArea = $configurationService->get('sendHttp2PushLimitToArea');
            $extensions = GeneralUtility::trimExplode(',', (string) $configurationService->get('sendHttp2PushFileExtensions'), true);

            $limitToAreaMatch = [];
            if (!empty($limitToArea) && preg_match("/<{$limitToArea}[^>]*>.+(?=<\\/{$limitToArea}>)/s", $content, $limitToAreaMatch)) {
                $content = (string) $limitToAreaMatch[0];
            }

            $event = new HttpPushHeaderEvent($headers, $content, $extensions);
            $this->eventDispatcher->dispatch($event);

            $headers = \array_slice($event->getHeaders(), 0, $limit);
        }

        return $headers;
    }
}

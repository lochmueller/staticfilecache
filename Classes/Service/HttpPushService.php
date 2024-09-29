<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Service;

use SFC\Staticfilecache\Service\HttpPush\AbstractHttpPush;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class HttpPushService
{
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

            foreach (GeneralUtility::makeInstance(ObjectFactoryService::class)->get('HttpPush') as $handler) {
                foreach ($extensions as $extension) {
                    /** @var AbstractHttpPush $handler */
                    if ($handler->canHandleExtension($extension)) {
                        $headers = array_merge($headers, $handler->getHeaders($content));
                    }
                }
            }

            $headers = \array_slice($headers, 0, $limit);
        }

        return $headers;
    }
}

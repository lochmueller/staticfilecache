<?php

/**
 * HttpPushService.
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Service;

use SFC\Staticfilecache\Service\HttpPush\AbstractHttpPush;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * HttpPushService.
 */
class HttpPushService extends AbstractService
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

            $limitToAreaMatches = [];
            if ('head' === $limitToArea) {
                preg_match_all('/<head[^>]*>.*<\/head>/s', $content, $limitToAreaMatches);
                $content = (string) $limitToAreaMatches[0][0];
            } elseif ('body' === $limitToArea) {
                preg_match_all('/<body[^>]*>.*<\/body>/s', $content, $limitToAreaMatches);
                $content = (string) $limitToAreaMatches[0][0];
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

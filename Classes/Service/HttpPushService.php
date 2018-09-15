<?php
/**
 * HttpPushService
 */

namespace SFC\Staticfilecache\Service;

use SFC\Staticfilecache\Service\HttpPush\AbstractHttpPush;
use SFC\Staticfilecache\Service\HttpPush\StyleHttpPush;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * HttpPushService
 */
class HttpPushService extends AbstractService
{

    /**
     * Get http push headers
     *
     * @param string $content
     * @return array
     */
    public function getHttpPushHeaders(string $content): array
    {
        $headers = [];
        /** @var ConfigurationService $configurationService */
        $configurationService = GeneralUtility::makeInstance(ConfigurationService::class);
        if ($configurationService->get('sendHttp2PushEnable')) {
            $limit = (int)$configurationService->get('sendHttp2PushFileLimit');
            $extensions = GeneralUtility::trimExplode(',', (string)$configurationService->get('sendHttp2PushFileExtensions'), true);
            $handlers = $this->getHttpPushHandler();

            foreach ($extensions as $extension) {
                foreach ($handlers as $handler) {
                    /** @var AbstractHttpPush $handler */
                    if ($handler->canHandleExtension($extension)) {
                        $headers += $handler->getHeaders($content);
                    }
                }
            }

            $headers = array_slice($headers, 0, $limit);
        }

        return $headers;
    }

    /**
     * Get HTTP push handlers
     *
     * @return array
     */
    protected function getHttpPushHandler(): array
    {

        // @todo create script, image and font Handler
        return [
            GeneralUtility::makeInstance(StyleHttpPush::class),
        ];
    }

}

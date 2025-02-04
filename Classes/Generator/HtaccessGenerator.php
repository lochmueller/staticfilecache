<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Generator;

use SFC\Staticfilecache\Event\GeneratorCreate;
use SFC\Staticfilecache\Event\GeneratorRemove;
use SFC\Staticfilecache\Service\ConfigurationService;
use SFC\Staticfilecache\Service\DateTimeService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

class HtaccessGenerator extends AbstractGenerator
{
    public function generate(GeneratorCreate $generatorCreateEvent): void
    {
        $configuration = GeneralUtility::makeInstance(ConfigurationService::class);

        $htaccessFile = PathUtility::pathinfo($generatorCreateEvent->getFileName(), PATHINFO_DIRNAME) . '/.htaccess';
        $accessTimeout = (int) $configuration->get('htaccessTimeout');
        $lifetime = $accessTimeout ?: $generatorCreateEvent->getLifetime();

        $headers = $configuration->getValidHeaders($generatorCreateEvent->getResponse()->getHeaders(), 'validHtaccessHeaders');
        if ($configuration->isBool('debugHeaders')) {
            $headers['X-SFC-State'] = 'StaticFileCache - via htaccess';
        }

        $contentType = 'text/html';
        if (isset($headers['Content-Type']) && preg_match('/[a-z\-]+\/[a-z\-]+/', $headers['Content-Type'], $matches)) {
            $contentType = $matches[0];
        }

        $sendCacheControlHeader = isset($GLOBALS['TSFE']->config['config']['sendCacheHeaders']) ? (bool) $GLOBALS['TSFE']->config['config']['sendCacheHeaders'] : false;

        $variables = [
            'contentType' => $contentType,
            'mode' => $accessTimeout ? 'A' : 'M',
            'lifetime' => $lifetime,
            'TIME' => '{TIME}',
            'expires' => (new DateTimeService())->getCurrentTime() + $lifetime,
            'sendCacheControlHeader' => $sendCacheControlHeader,
            'sendCacheControlHeaderRedirectAfterCacheTimeout' => $configuration->isBool('sendCacheControlHeaderRedirectAfterCacheTimeout'),
            'headers' => $this->cleanupHeaderValues($headers),
        ];

        $this->renderTemplateToFile($this->getTemplateName(), $variables, $htaccessFile);
    }

    protected function cleanupHeaderValues(array $headers): array
    {
        $configurationService = $this->getConfigurationService();
        $maxHeaderSize = (int)($configurationService->get('maxHeaderSize') ?? 8192);
        $headerSizeBuffer = (float)($configurationService->get('headerSizeBuffer') ?? 0);

        if($headerSizeBuffer > 0){
            $maxHeaderSize = (int)($maxHeaderSize / $headerSizeBuffer);
        }
        // respect max length
        foreach ($headers as $key => $value) {
            $headers[$key] = substr((string) $value, 0, $maxHeaderSize); // 8K Max header for Apache
        }

        // illegal chars
        $headers = array_map(fn($item) => str_replace('"', '\"', $item), $headers);

        return $headers;
    }

    public function remove(GeneratorRemove $generatorRemoveEvent): void
    {
        $htaccessFile = PathUtility::pathinfo($generatorRemoveEvent->getFileName(), PATHINFO_DIRNAME) . '/.htaccess';
        $this->removeFile($htaccessFile);
    }

    /**
     * Get the template name.
     */
    protected function getTemplateName(): string
    {
        $configuration = GeneralUtility::makeInstance(ConfigurationService::class);
        $templateName = trim((string) $configuration->get('htaccessTemplateName'));
        if ('' === $templateName) {
            return 'EXT:staticfilecache/Resources/Private/Templates/Htaccess.html';
        }

        return $templateName;
    }
}

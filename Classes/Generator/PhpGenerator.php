<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Generator;

use Psr\Http\Message\ResponseInterface;
use SFC\Staticfilecache\Service\ConfigurationService;
use SFC\Staticfilecache\Service\DateTimeService;
use SFC\Staticfilecache\Service\RemoveService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

/**
 * PlainGenerator.
 */
class PhpGenerator extends HtaccessGenerator
{
    /**
     * Generate file.
     */
    public function generate(string $entryIdentifier, string $fileName, ResponseInterface $response, int $lifetime): void
    {
        $configuration = GeneralUtility::makeInstance(ConfigurationService::class);
        $accessTimeout = (int) $configuration->get('htaccessTimeout');
        $lifetime = $accessTimeout ?: $lifetime;

        $headers = $configuration->getValidHeaders($response->getHeaders(), 'validHtaccessHeaders');
        if ($configuration->isBool('debugHeaders')) {
            $headers['X-SFC-State'] = 'StaticFileCache - via PhpGenerator';
        }
        $headers = array_map(fn($item) => str_replace("'", "\'", $item), $headers);
        $requestUri = GeneralUtility::getIndpEnv('REQUEST_URI');

        $variables = [
            'expires' => (new DateTimeService())->getCurrentTime() + $lifetime,
            'sendCacheControlHeaderRedirectAfterCacheTimeout' => $configuration->isBool('sendCacheControlHeaderRedirectAfterCacheTimeout'),
            'headers' => $headers,
            'requestUri' => $requestUri,
            'body' => (string) $response->getBody(),
        ];

        $this->renderTemplateToFile($this->getTemplateName(), $variables, $fileName . '.php');
    }

    /**
     * Remove file.
     */
    public function remove(string $entryIdentifier, string $fileName): void
    {
        $removeService = GeneralUtility::makeInstance(RemoveService::class);
        $removeService->file($fileName . '.php');
    }

    /**
     * Get the template name.
     */
    protected function getTemplateName(): string
    {
        $configuration = GeneralUtility::makeInstance(ConfigurationService::class);
        $templateName = trim((string) $configuration->get('phpTemplateName'));
        if ('' === $templateName) {
            return 'EXT:staticfilecache/Resources/Private/Templates/Php.html';
        }

        return $templateName;
    }
}

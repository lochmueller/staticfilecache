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

        $sendCacheControlHeader = isset($GLOBALS['TSFE']->config['config']['sendCacheHeaders']) ? (bool) $GLOBALS['TSFE']->config['config']['sendCacheHeaders'] : false;
        $headers = $this->getReponseHeaders($response);
        if ($configuration->isBool('debugHeaders')) {
            $headers['X-SFC-State'] = 'StaticFileCache - via htaccess';
            $headers['X-SFC-Generator'] = 'PhpGenerator';
        }
        $headers = array_map(fn ($item) => str_replace('"', '\"', $item), $headers);
        $requestUri = GeneralUtility::getIndpEnv('REQUEST_URI');

        $variables = [
            'lifetime' => $lifetime,
            'TIME' => '{TIME}',
            'expires' => (new DateTimeService())->getCurrentTime() + $lifetime,
            'sendCacheControlHeader' => $sendCacheControlHeader,
            'sendCacheControlHeaderRedirectAfterCacheTimeout' => $configuration->isBool('sendCacheControlHeaderRedirectAfterCacheTimeout'),
            'headers' => $headers,
            'requestUri' => $requestUri,
            'body' => (string) $response->getBody(),
        ];

        $this->renderTemplateToFile($this->getTemplateName(), $variables, $fileName.'.php');
    }

    /**
     * Remove file.
     */
    public function remove(string $entryIdentifier, string $fileName): void
    {
        $removeService = GeneralUtility::makeInstance(RemoveService::class);
        $removeService->file($fileName.'.php');
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

<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Generator;

use Psr\Http\Message\ResponseInterface;
use SFC\Staticfilecache\Service\ConfigurationService;
use SFC\Staticfilecache\Service\DateTimeService;
use SFC\Staticfilecache\Service\RemoveService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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

        $headers = $this->getReponseHeaders($response);
        if ($configuration->isBool('debugHeaders')) {
            $headers['X-SFC-State'] = 'StaticFileCache - via htaccess';
            $headers['X-SFC-Generator'] = 'PhpGenerator';
        }
        $headers = array_map(fn ($item) => str_replace('"', '\"', $item), $headers);

        $currentTime = (new DateTimeService())->getCurrentTime();
        $expires = $currentTime + $lifetime;
        $requestUri = GeneralUtility::getIndpEnv('REQUEST_URI');

        $methodCalls = [];
        if ($configuration->isBool('sendCacheControlHeaderRedirectAfterCacheTimeout')) {
            $methodCalls[] = "/* expires on " . date('Y-m-d H:i:s', $expires) . " */ if(time() > {$expires}) { unlink(__FILE__);header('Location: {$requestUri}'); };";
        }

        foreach ($headers as $header => $value) {
            $methodCalls[] = "header('{$header}:{$value}');";
        }

        $php = '<?php /* generated on ' . date('r', $currentTime) . ' */ '. implode('', $methodCalls) . ' ?>';
        GeneralUtility::writeFile($fileName.'.php', $php . (string) $response->getBody());
    }

    /**
     * Remove file.
     */
    public function remove(string $entryIdentifier, string $fileName): void
    {
        $removeService = GeneralUtility::makeInstance(RemoveService::class);
        $removeService->file($fileName.'.php');
    }
}

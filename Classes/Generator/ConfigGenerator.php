<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Generator;

use Psr\Http\Message\ResponseInterface;
use SFC\Staticfilecache\Service\ConfigurationService;
use SFC\Staticfilecache\Service\RemoveService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ConfigGenerator extends AbstractGenerator
{
    public function generate(string $entryIdentifier, string $fileName, ResponseInterface $response, int $lifetime): void
    {
        $config = [
            'generated' => date('r'),
            'headers' => GeneralUtility::makeInstance(ConfigurationService::class)
                ->getValidHeaders($response->getHeaders(), 'validFallbackHeaders'),
        ];
        $this->writeFile($fileName . '.config.json', json_encode($config, JSON_PRETTY_PRINT));
    }

    public function remove(string $entryIdentifier, string $fileName): void
    {
        $this->removeFile($fileName . '.config.json');
    }
}

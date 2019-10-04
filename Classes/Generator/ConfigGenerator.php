<?php
/**
 * ConfigGenerator.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Generator;

use SFC\Staticfilecache\Service\MiddlewareService;
use SFC\Staticfilecache\Service\RemoveService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * ConfigGenerator.
 */
class ConfigGenerator extends AbstractGenerator
{
    /**
     * Generate file.
     *
     * @param string $entryIdentifier
     * @param string $fileName
     * @param string $data
     * @param int $lifetime
     */
    public function generate(string $entryIdentifier, string $fileName, string &$data, int $lifetime): void
    {
        $config = [
            'generated' => date('r'),
            'headers' =>  MiddlewareService::getResponse()->getHeaders()
        ];
        GeneralUtility::writeFile($fileName . '.config.json', \json_encode($config, JSON_PRETTY_PRINT));
    }

    /**
     * Remove file.
     *
     * @param string $entryIdentifier
     * @param string $fileName
     */
    public function remove(string $entryIdentifier, string $fileName): void
    {
        $removeService = GeneralUtility::makeInstance(RemoveService::class);
        $removeService->file($fileName . '.config.json');
    }
}

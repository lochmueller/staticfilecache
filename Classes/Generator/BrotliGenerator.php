<?php
/**
 * BrotliGenerator.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Generator;

use SFC\Staticfilecache\Service\RemoveService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * BrotliGenerator.
 */
class BrotliGenerator extends AbstractGenerator
{
    /**
     * Generate file.
     *
     * @param string $entryIdentifier
     * @param string $fileName
     * @param string $data
     */
    public function generate(string $entryIdentifier, string $fileName, string $data)
    {
        $contentCompress = \brotli_compress($data);
        if ($contentCompress) {
            GeneralUtility::writeFile($fileName . '.br', $contentCompress);
        }
    }

    /**
     * Remove file.
     *
     * @param string $entryIdentifier
     * @param string $fileName
     */
    public function remove(string $entryIdentifier, string $fileName)
    {
        $removeService = GeneralUtility::makeInstance(RemoveService::class);
        $removeService->removeFile($fileName . '.br');
    }
}

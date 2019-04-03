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
        if (!$this->checkAvailable()) {
            return;
        }
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
        if (!$this->checkAvailable()) {
            return;
        }
        $removeService = GeneralUtility::makeInstance(RemoveService::class);
        $removeService->removeFile($fileName . '.br');
    }

    /**
     * Check if Brotli is available.
     *
     * @return bool
     */
    protected function checkAvailable(): bool
    {
        $available = \function_exists('brotli_compress');
        if (!$available) {
            $this->logger->error('Your server do not support Botli compression, but you enable Brotli in EXT:staticfilecache configuration');
        }

        return $available;
    }
}

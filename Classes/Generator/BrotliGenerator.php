<?php
/**
 * BrotliGenerator.
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Generator;

use Psr\Http\Message\ResponseInterface;
use SFC\Staticfilecache\Service\RemoveService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * BrotliGenerator.
 */
class BrotliGenerator extends AbstractGenerator
{
    /**
     * Generate file.
     */
    public function generate(string $entryIdentifier, string $fileName, ResponseInterface $response, int $lifetime): void
    {
        if (!$this->checkAvailable()) {
            return;
        }
        $contentCompress = brotli_compress((string) $response->getBody());
        if ($contentCompress) {
            GeneralUtility::writeFile($fileName.'.br', $contentCompress);
        }
    }

    /**
     * Remove file.
     */
    public function remove(string $entryIdentifier, string $fileName): void
    {
        if (!$this->checkAvailable()) {
            return;
        }
        $removeService = GeneralUtility::makeInstance(RemoveService::class);
        $removeService->file($fileName.'.br');
    }

    /**
     * Check if Brotli is available.
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

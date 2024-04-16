<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Generator;

use Psr\Http\Message\ResponseInterface;
use SFC\Staticfilecache\Service\RemoveService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class BrotliGenerator extends AbstractGenerator
{
    public function generate(string $entryIdentifier, string $fileName, ResponseInterface $response, int $lifetime): void
    {
        if (!$this->checkAvailable()) {
            return;
        }
        $contentCompress = brotli_compress((string) $response->getBody());
        if ($contentCompress) {
            $this->writeFile($fileName . '.br', $contentCompress);
        }
    }

    public function remove(string $entryIdentifier, string $fileName): void
    {
        if (!$this->checkAvailable()) {
            return;
        }
        $this->removeFile($fileName . '.br');
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

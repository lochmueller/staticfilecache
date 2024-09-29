<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Generator;

use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use SFC\Staticfilecache\Event\GeneratorCreate;
use SFC\Staticfilecache\Event\GeneratorRemove;
use SFC\Staticfilecache\Service\RemoveService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class BrotliGenerator extends AbstractGenerator implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function generate(GeneratorCreate $generatorCreateEvent): void
    {
        if (!$this->checkAvailable()) {
            return;
        }
        $contentCompress = brotli_compress((string) $generatorCreateEvent->getResponse()->getBody());
        if ($contentCompress) {
            $this->writeFile($generatorCreateEvent->getFileName() . '.br', $contentCompress);
        }
    }

    public function remove(GeneratorRemove $generatorRemoveEvent): void
    {
        if (!$this->checkAvailable()) {
            return;
        }
        $this->removeFile($generatorRemoveEvent->getFileName() . '.br');
    }

    /**
     * Check if Brotli is available.
     */
    protected function checkAvailable(): bool
    {
        if (!$this->getConfigurationService()->get('enableGeneratorBrotli')) {
            return false;
        }

        $available = \function_exists('brotli_compress');
        if (!$available) {
            $this->logger->error('Your server do not support Botli compression, but you enable Brotli in EXT:staticfilecache configuration');
        }

        return $available;
    }
}

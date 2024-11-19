<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Generator;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use SFC\Staticfilecache\Event\GeneratorContentManipulationEvent;
use SFC\Staticfilecache\Event\GeneratorCreate;
use SFC\Staticfilecache\Event\GeneratorRemove;

class BrotliGenerator extends AbstractGenerator implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function generate(GeneratorCreate $generatorCreateEvent): void
    {
        if (!$this->checkAvailable()) {
            return;
        }
        /** @var GeneratorContentManipulationEvent  $contentManipulationEvent */
        $contentManipulationEvent = $this->eventDispatcher->dispatch(new GeneratorContentManipulationEvent((string) $generatorCreateEvent->getResponse()->getBody()));
        $contentCompress = brotli_compress($contentManipulationEvent->getContent());
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
            $this->logger->error('Your server does not support Brotli compression, but you enabled Brotli in EXT:staticfilecache configuration');
        }

        return $available;
    }
}

<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Generator;

use Psr\Http\Message\ResponseInterface;
use SFC\Staticfilecache\Event\GeneratorContentManipulationEvent;
use SFC\Staticfilecache\Event\GeneratorCreate;
use SFC\Staticfilecache\Event\GeneratorRemove;
use SFC\Staticfilecache\Service\RemoveService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

class GzipGenerator extends AbstractGenerator
{
    /**
     * The default compression level.
     */
    public const DEFAULT_COMPRESSION_LEVEL = 3;

    public function generate(GeneratorCreate $generatorCreateEvent): void
    {

        if (!$this->getConfigurationService()->get('enableGeneratorGzip')) {
            return;
        }
        /** @var GeneratorContentManipulationEvent  $contentManipulationEvent */
        $contentManipulationEvent = $this->eventDispatcher->dispatch(new GeneratorContentManipulationEvent((string) $generatorCreateEvent->getResponse()->getBody()));
        $contentGzip = gzencode($contentManipulationEvent->getContent(), $this->getCompressionLevel());
        if ($contentGzip) {
            $this->writeFile($generatorCreateEvent->getFileName() . '.gz', $contentGzip);
        }
    }

    public function remove(GeneratorRemove $generatorRemoveEvent): void
    {

        if (!$this->getConfigurationService()->get('enableGeneratorGzip')) {
            return;
        }
        $this->removeFile($generatorRemoveEvent->getFileName() . '.gz');
    }

    /**
     * Get frontend compression level.
     * The value is between 1 (low) and 9 (high).
     */
    protected function getCompressionLevel(): int
    {
        $level = self::DEFAULT_COMPRESSION_LEVEL;
        if (isset($GLOBALS['TYPO3_CONF_VARS']['FE']['compressionLevel'])) {
            $level = (int) $GLOBALS['TYPO3_CONF_VARS']['FE']['compressionLevel'];
        }

        return MathUtility::forceIntegerInRange($level, 1, 9, self::DEFAULT_COMPRESSION_LEVEL);
    }
}

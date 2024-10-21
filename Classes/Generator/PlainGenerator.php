<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Generator;

use SFC\Staticfilecache\Event\GeneratorContentManipulationEvent;
use SFC\Staticfilecache\Event\GeneratorCreate;
use SFC\Staticfilecache\Event\GeneratorRemove;

class PlainGenerator extends AbstractGenerator
{
    public function generate(GeneratorCreate $generatorCreateEvent): void
    {

        if (!$this->getConfigurationService()->get('enableGeneratorPlain')) {
            return;
        }
        /** @var GeneratorContentManipulationEvent  $contentManipulationEvent */
        $contentManipulationEvent = $this->eventDispatcher->dispatch(new GeneratorContentManipulationEvent((string) $generatorCreateEvent->getResponse()->getBody()));

        $this->writeFile($generatorCreateEvent->getFileName(), $contentManipulationEvent->getContent());
    }

    public function remove(GeneratorRemove $generatorRemoveEvent): void
    {

        if (!$this->getConfigurationService()->get('enableGeneratorPlain')) {
            return;
        }
        $this->removeFile($generatorRemoveEvent->getFileName());
    }
}

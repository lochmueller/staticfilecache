<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Generator;

use SFC\Staticfilecache\Event\GeneratorCreate;
use SFC\Staticfilecache\Event\GeneratorRemove;

class PlainGenerator extends AbstractGenerator
{
    public function generate(GeneratorCreate $generatorCreateEvent): void
    {

        if (!$this->getConfigurationService()->get('enableGeneratorPlain')) {
            return;
        }
        $this->writeFile($generatorCreateEvent->getFileName(), (string) $generatorCreateEvent->getResponse()->getBody());
    }

    public function remove(GeneratorRemove $generatorRemoveEvent): void
    {

        if (!$this->getConfigurationService()->get('enableGeneratorPlain')) {
            return;
        }
        $this->removeFile($generatorRemoveEvent->getFileName());
    }
}

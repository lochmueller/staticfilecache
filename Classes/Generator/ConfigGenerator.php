<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Generator;

use Psr\Http\Message\ResponseInterface;
use SFC\Staticfilecache\Event\GeneratorConfigManipulationEvent;
use SFC\Staticfilecache\Event\GeneratorCreate;
use SFC\Staticfilecache\Event\GeneratorRemove;
use SFC\Staticfilecache\Service\ConfigurationService;
use SFC\Staticfilecache\Service\RemoveService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ConfigGenerator extends AbstractGenerator
{
    public function generate(GeneratorCreate $generatorCreateEvent): void
    {
        $config = [
            'generated' => date('r'),
            'headers' => GeneralUtility::makeInstance(ConfigurationService::class)
                ->getValidHeaders($generatorCreateEvent->getResponse()->getHeaders(), 'validFallbackHeaders'),
        ];
        /** @var GeneratorConfigManipulationEvent  $configManipulationEvent */
        $configManipulationEvent = $this->eventDispatcher->dispatch(new GeneratorConfigManipulationEvent($config));

        $this->writeFile($generatorCreateEvent->getFileName() . '.config.json', json_encode($configManipulationEvent->getConfig(), JSON_PRETTY_PRINT));
    }

    public function remove(GeneratorRemove $generatorRemoveEvent): void
    {
        $this->removeFile($generatorRemoveEvent->getFileName() . '.config.json');
    }
}

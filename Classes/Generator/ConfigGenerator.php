<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Generator;

use Psr\Http\Message\ServerRequestInterface;
use SFC\Staticfilecache\Event\GeneratorConfigManipulationEvent;
use SFC\Staticfilecache\Event\GeneratorCreate;
use SFC\Staticfilecache\Event\GeneratorRemove;
use SFC\Staticfilecache\Service\CacheHeaderService;
use SFC\Staticfilecache\Service\ConfigurationService;
use SFC\Staticfilecache\Service\DateTimeService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ConfigGenerator extends AbstractGenerator
{
    public function generate(GeneratorCreate $generatorCreateEvent): void
    {
        $time = (new DateTimeService())->getCurrentTime();
        $request = ($GLOBALS['TYPO3_REQUEST'] ?? null) instanceof ServerRequestInterface ? $GLOBALS['TYPO3_REQUEST'] : null;
        $config = [
            'generated' => date('r', $time),
            'generatedTimestamp' => $time,
            'invalidAt' => date('r', $time + $generatorCreateEvent->getLifetime()),
            'invalidAtTimestamp' => $time + $generatorCreateEvent->getLifetime(),
            'headers' => GeneralUtility::makeInstance(ConfigurationService::class)
                ->getValidHeaders($generatorCreateEvent->getResponse()->getHeaders(), 'validFallbackHeaders'),
            // Evaluated by the fallback middleware on every delivery, so it can
            // send the remaining lifetime and decide "auto" per request.
            ...GeneralUtility::makeInstance(CacheHeaderService::class)->getTypoScriptConfiguration($request),
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

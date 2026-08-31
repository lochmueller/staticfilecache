<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Cache\Listener;

use SFC\Staticfilecache\Event\CacheRuleEvent;
use SFC\Staticfilecache\Service\ConfigurationService;
use TYPO3\CMS\Frontend\Cache\NonceValueSubstitution;
use TYPO3\CMS\Frontend\Page\PageParts;

class NoIntScriptsListener
{
    public function __construct(protected readonly ConfigurationService $configurationService) {}

    public function __invoke(CacheRuleEvent $event): void
    {
        // @todo PageParts is marked "@internal Experimental, will change" by the TYPO3 core
        //       and may change during v14/v15 development. Re-check this listener with
        //       every major TYPO3 update, see Breaking-107831/107578 (TSFE removal series).
        $pageParts = $event->getRequest()->getAttribute('frontend.page.parts');
        if (!$pageParts instanceof PageParts) {
            return;
        }
        $cspGenerationOverride = (bool) $this->configurationService->get('cspGenerationOverride');
        foreach ($pageParts->getNotCachedContentElementRegistry() as $key => $configuration) {
            if ($cspGenerationOverride
                && isset($configuration['target'])
                && $configuration['target'] === NonceValueSubstitution::class . '->substituteNonce'
            ) {
                continue;
            }

            $event->addExplanation(
                __CLASS__ . ':' . $key,
                'The page has a not-cached content element: ' . implode(', ', $this->getInformation($configuration))
            );
        }
    }

    /**
     * Get the debug information.
     *
     * @param array<string, mixed> $configuration
     *
     * @return array<int, string>
     */
    protected function getInformation(array $configuration): array
    {
        $info = [];

        // Root properties
        foreach ([
            'substKey',
            'target',
            'type',
        ] as $value) {
            if (isset($configuration[$value])) {
                $info[] = $value . ': ' . $configuration[$value];
            }
        }

        // Conf properties
        foreach ([
            'userFunc',
            'includeLibs',
            'extensionName',
            'pluginName',
        ] as $value) {
            if (isset($configuration['conf'][$value])) {
                $info[] = $value . ': ' . $configuration['conf'][$value];
            }
        }

        return $info;
    }
}

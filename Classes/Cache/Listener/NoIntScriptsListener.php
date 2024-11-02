<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Cache\Listener;

use SFC\Staticfilecache\Event\CacheRuleEvent;
use TYPO3\CMS\Frontend\Cache\NonceValueSubstitution;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class NoIntScriptsListener
{
    public function __invoke(CacheRuleEvent $event): void
    {
        $tsfe = $GLOBALS['TSFE'] ?? null;
        if ($tsfe instanceof TypoScriptFrontendController && $tsfe->isINTincScript()) {
            foreach ((array) $tsfe->config['INTincScript'] as $key => $configuration) {

                // Check CSP Handling
                // if (isset($configuration['target']) && $configuration['target'] === NonceValueSubstitution::class . '->substituteNonce') {
                //     continue;
                // }

                $event->addExplanation(__CLASS__ . ':' . $key, 'The page has a INTincScript: ' . implode(', ', $this->getInformation($configuration)));
            }
        }
    }

    /**
     * Get the debug information.
     *
     * @param array $configuration
     */
    protected function getInformation($configuration): array
    {
        $info = [];

        // Root properties
        foreach ([
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

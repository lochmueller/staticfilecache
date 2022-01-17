<?php

/**
 * No _INT scripts.
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Cache\Rule;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * No _INT scripts.
 */
class NoIntScripts extends AbstractRule
{
    /**
     * Check if there are no _INT scripts.
     */
    public function checkRule(ServerRequestInterface $request, array &$explanation, bool &$skipProcessing): void
    {
        $tsfe = $GLOBALS['TSFE'] ?? null;
        if ($tsfe instanceof TypoScriptFrontendController && $tsfe->isINTincScript()) {
            foreach ((array) $tsfe->config['INTincScript'] as $key => $configuration) {
                $explanation[__CLASS__.':'.$key] = 'The page has a INTincScript: '.implode(', ', $this->getInformation($configuration));
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
        if (isset($configuration['type'])) {
            $info[] = 'type: '.$configuration['type'];
        }
        $check = [
            'userFunc',
            'includeLibs',
            'extensionName',
            'pluginName',
        ];
        foreach ($check as $value) {
            if (isset($configuration['conf'][$value])) {
                $info[] = $value.': '.$configuration['conf'][$value];
            }
        }

        return $info;
    }
}

<?php
/**
 * No _INT scripts
 *
 * @package SFC\NcStaticfilecache\Cache\Rule
 * @author  Tim Lochmüller
 */

namespace SFC\NcStaticfilecache\Cache\Rule;

use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * No _INT scripts
 *
 * @author Tim Lochmüller
 */
class NoIntScripts extends AbstractRule
{

    /**
     * Check if there are no _INT scripts
     *
     * @param TypoScriptFrontendController $frontendController
     * @param string $uri
     * @param array $explanation
     * @param bool $skipProcessing
     *
     * @return array
     */
    public function checkRule($frontendController, $uri, &$explanation, &$skipProcessing)
    {
        if ($frontendController->isINTincScript()) {
            foreach ($frontendController->config['INTincScript'] as $key => $value) {
                $info = [];
                if (isset($value['type'])) {
                    $info[] = 'type: ' . $value['type'];
                }
                if (isset($value['conf']['userFunc'])) {
                    $info[] = 'userFunc: ' . $value['conf']['userFunc'];
                }
                if (isset($value['conf']['includeLibs'])) {
                    $info[] = 'includeLibs: ' . $value['conf']['includeLibs'];
                }
                if (isset($value['conf']['extensionName'])) {
                    $info[] = 'extensionName: ' . $value['conf']['extensionName'];
                }
                if (isset($value['conf']['pluginName'])) {
                    $info[] = 'pluginName: ' . $value['conf']['pluginName'];
                }
                $explanation[__CLASS__ . ':' . $key] = 'The page has a INTincScript: ' . implode(', ', $info);
            }
        }
    }
}

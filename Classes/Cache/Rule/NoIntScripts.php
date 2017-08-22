<?php
/**
 * No _INT scripts.
 */
declare(strict_types=1);

namespace SFC\Staticfilecache\Cache\Rule;

use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * No _INT scripts.
 */
class NoIntScripts extends AbstractRule
{
    /**
     * Check if there are no _INT scripts.
     *
     * @param TypoScriptFrontendController $frontendController
     * @param string                       $uri
     * @param array                        $explanation
     * @param bool                         $skipProcessing
     */
    public function checkRule(TypoScriptFrontendController $frontendController, string $uri, array &$explanation, bool &$skipProcessing)
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

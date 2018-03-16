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
                $explanation[__CLASS__ . ':' . $key] = 'The page has a INTincScript: ' . implode(', ', $this->getInformation($value));
            }
        }
    }

    /**
     * Get the debug information.
     *
     * @param array $configuration
     *
     * @return array
     */
    protected function getInformation($configuration): array
    {
        $info = [];
        if (isset($value['type'])) {
            $info[] = 'type: ' . $value['type'];
        }
        $check = [
            'userFunc',
            'includeLibs',
            'extensionName',
            'pluginName',
        ];
        foreach ($check as $value) {
            if (isset($value['conf'][$value])) {
                $info[] = $value . ': ' . $value['conf'][$value];
            }
        }

        return $info;
    }
}

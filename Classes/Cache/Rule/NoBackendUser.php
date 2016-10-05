<?php
/**
 * No active BE user
 *
 * @author  Tim Lochmüller
 */

namespace SFC\Staticfilecache\Cache\Rule;

use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * No active BE user
 */
class NoBackendUser extends AbstractRule
{

    /**
     * No active BE user
     *
     * @param TypoScriptFrontendController $frontendController
     * @param string $uri
     * @param array $explanation
     * @param bool $skipProcessing
     */
    public function checkRule($frontendController, $uri, &$explanation, &$skipProcessing)
    {
        if ($frontendController->beUserLogin) {
            $skipProcessing = true;
            $explanation[__CLASS__] = 'Active BE Login (TSFE:beUserLogin)';
        }
    }
}

<?php
/**
 * LoginDeniedConfiguration
 *
 * @author  Tim Lochmüller
 */

namespace SFC\Staticfilecache\Cache\Rule;

use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * LoginDeniedConfiguration
 */
class LoginDeniedConfiguration extends AbstractRule
{

    /**
     * Check LoginDeniedConfiguration
     *
     * @param TypoScriptFrontendController $frontendController
     * @param string $uri
     * @param array $explanation
     * @param bool $skipProcessing
     */
    public function checkRule($frontendController, $uri, &$explanation, &$skipProcessing)
    {
        $loginDeniedCfg = (!$frontendController->config['config']['sendCacheHeaders_onlyWhenLoginDeniedInBranch'] || !$frontendController->loginAllowedInBranch);
        if (!$loginDeniedCfg) {
            $explanation[__CLASS__] = 'LoginDeniedCfg is true';
        }
    }
}

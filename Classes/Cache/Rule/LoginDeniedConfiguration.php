<?php

/**
 * LoginDeniedConfiguration.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Cache\Rule;

use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * LoginDeniedConfiguration.
 */
class LoginDeniedConfiguration extends AbstractRule
{
    /**
     * Check LoginDeniedConfiguration.
     *
     * @param TypoScriptFrontendController $frontendController
     * @param string                       $uri
     * @param array                        $explanation
     * @param bool                         $skipProcessing
     */
    public function checkRule(TypoScriptFrontendController $frontendController, string $uri, array &$explanation, bool &$skipProcessing)
    {
        $name = 'sendCacheHeaders_onlyWhenLoginDeniedInBranch';
        $loginDeniedCfg = (!$frontendController->config['config'][$name] || !$frontendController->loginAllowedInBranch);
        if (!$loginDeniedCfg) {
            $explanation[__CLASS__] = 'LoginDeniedCfg is true';
        }
    }
}

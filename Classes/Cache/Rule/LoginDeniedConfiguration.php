<?php
/**
 * LoginDeniedConfiguration
 *
 * @package SFC\NcStaticfilecache\Cache\Rule
 * @author  Tim Lochmüller
 */

namespace SFC\NcStaticfilecache\Cache\Rule;

use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * LoginDeniedConfiguration
 *
 * @author Tim Lochmüller
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
     *
     * @return array
     */
    public function checkRule($frontendController, $uri, &$explanation, &$skipProcessing)
    {
        $loginDeniedCfg = (!$frontendController->config['config']['sendCacheHeaders_onlyWhenLoginDeniedInBranch'] || !$frontendController->loginAllowedInBranch);
        if (!$loginDeniedCfg) {
            $explanation[__CLASS__] = 'LoginDeniedCfg is true';
        }
    }
}

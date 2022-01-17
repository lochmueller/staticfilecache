<?php

/**
 * LoginDeniedConfiguration.
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Cache\Rule;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * LoginDeniedConfiguration.
 */
class LoginDeniedConfiguration extends AbstractRule
{
    /**
     * Check LoginDeniedConfiguration.
     */
    public function checkRule(ServerRequestInterface $request, array &$explanation, bool &$skipProcessing): void
    {
        $tsfe = $GLOBALS['TSFE'] ?? null;
        if (!($tsfe instanceof TypoScriptFrontendController)) {
            return;
        }
        $name = 'sendCacheHeaders_onlyWhenLoginDeniedInBranch';
        $loginDeniedCfg = (!($tsfe->config['config'][$name] ?? false) || !$tsfe->checkIfLoginAllowedInBranch());
        if (!$loginDeniedCfg) {
            $explanation[__CLASS__] = 'LoginDeniedCfg is true';
        }
    }
}

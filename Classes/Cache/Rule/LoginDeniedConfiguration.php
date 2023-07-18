<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Cache\Rule;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * LoginDeniedConfiguration.
 * @deprecated can be removed if TYPO3 11 is not supported anymore.
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
        // @deprecated method was removed with TYPO3 12
        if(!method_exists($tsfe, 'checkIfLoginAllowedInBranch')){
            return;
        }
        $name = 'sendCacheHeaders_onlyWhenLoginDeniedInBranch';
        $configActive = $tsfe->config['config'][$name] ?? false;
        if ($configActive && $tsfe->checkIfLoginAllowedInBranch()) {
            $explanation[__CLASS__] = 'LoginDeniedCfg is true';
        }
    }
}

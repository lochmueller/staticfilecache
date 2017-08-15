<?php
/**
 * No active BE user
 *
 */
declare(strict_types=1);

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
    public function checkRule(TypoScriptFrontendController $frontendController, string $uri, array &$explanation, bool &$skipProcessing)
    {
        if ($frontendController->isBackendUserLoggedIn() || $this->hasActiveBackendCookies()) {
            $skipProcessing = true;
            $explanation[__CLASS__] = 'Active BE Login or active BE login session (TSFE:beUserLogin)';
        }
    }

    /**
     * Has active backend cookies
     *
     * The Server rewrite rules can only check the existence of an cookie and not the valid
     * auth process behind. This is the reason, why we call a user with a BE cookie "active"
     * even if the users cookie is actually a invalid/old one.
     *
     * @return bool
     */
    protected function hasActiveBackendCookies(): bool
    {
        $cookieNames = [
            'be_typo_user',
            $GLOBALS['TYPO3_CONF_VARS']['BE']['cookieName']
        ];
        foreach ($cookieNames as $cookieName) {
            if (isset($_COOKIE[$cookieName])) {
                return true;
            }
        }
        return false;
    }
}

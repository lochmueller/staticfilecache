<?php
/**
 * Init frontend user
 *
 * @author  Tim Lochmüller
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Hook;

use SFC\Staticfilecache\Utility\CookieUtility;
use SFC\Staticfilecache\Utility\DateTimeUtility;

/**
 * Init frontend user
 */
class InitFrontendUser extends AbstractHook
{

    /**
     * Set a cookie if a user logs in or refresh it
     *
     * This function is needed because TYPO3 always sets the fe_typo_user cookie,
     * even if the user never logs in. We want to be able to check against logged
     * in frontend users from mod_rewrite. So we need to set our own cookie (when
     * a user actually logs in).
     *
     * Checking code taken from class.t3lib_userauth.php
     *
     * @param    object $params : parameter array
     * @param    object $pObj : partent object
     *
     * @return    void
     */
    public function setFeUserCookie(&$params, &$pObj)
    {
        if ($pObj->fe_user->dontSetCookie) {
            // do not set any cookie
            return;
        }

        $started = $pObj->fe_user->loginSessionStarted;

        if (($started || $pObj->fe_user->forceSetCookie) && $pObj->fe_user->lifetime == 0) {
            // If new session and the cookie is a sessioncookie, we need to set it only once!
            // // isSetSessionCookie()
            CookieUtility::setCookie(0);
        } elseif (($started || isset($_COOKIE[CookieUtility::FE_COOKIE_NAME])) && $pObj->fe_user->lifetime > 0) {
            // If it is NOT a session-cookie, we need to refresh it.
            // isRefreshTimeBasedCookie()
            CookieUtility::setCookie(DateTimeUtility::getCurrentTime() + $pObj->fe_user->lifetime);
        }
    }
}

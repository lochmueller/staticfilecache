<?php

/**
 * Init frontend user.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Hook;

use SFC\Staticfilecache\Service\CookieService;
use SFC\Staticfilecache\Service\DateTimeService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Init frontend user.
 */
class InitFrontendUser extends AbstractHook
{
    /**
     * Set a cookie if a user logs in or refresh it.
     *
     * This function is needed because TYPO3 always sets the fe_typo_user cookie,
     * even if the user never logs in. We want to be able to check against logged
     * in frontend users from mod_rewrite. So we need to set our own cookie (when
     * a user actually logs in).
     *
     * Checking code taken from \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication
     *
     * @param array  $parameters
     * @param object $parentObject
     */
    public function setFeUserCookie(&$parameters, $parentObject)
    {
        if ($parentObject->fe_user->dontSetCookie) {
            // do not set any cookie
            return;
        }

        $started = $parentObject->fe_user->loginSessionStarted;

        $cookieService = GeneralUtility::makeInstance(CookieService::class);
        if (($started || $parentObject->fe_user->forceSetCookie) && 0 === $parentObject->fe_user->lifetime) {
            // If new session and the cookie is a sessioncookie, we need to set it only once!
            // // isSetSessionCookie()
            $cookieService->setCookie(0);
        } elseif (($started || isset($_COOKIE[CookieService::FE_COOKIE_NAME])) && $parentObject->fe_user->lifetime > 0) {
            // If it is NOT a session-cookie, we need to refresh it.
            // isRefreshTimeBasedCookie()
            $cookieService->setCookie((new DateTimeService())->getCurrentTime() + $parentObject->fe_user->lifetime);
        }
    }
}

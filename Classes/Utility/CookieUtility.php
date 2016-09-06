<?php

/**
 * Handle cookie related stuff
 */

namespace SFC\NcStaticfilecache\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Handle cookie related stuff
 */
class CookieUtility
{

    /**
     * The name of the cookie
     */
    const FE_COOKIE_NAME = 'nc_staticfilecache';

    /**
     * Set the Cookie
     *
     * @param $lifetime
     */
    public static function setCookie($lifetime)
    {
        $cookieDomain = self::getCookieDomain();
        setcookie(self::FE_COOKIE_NAME, 'fe_typo_user_logged_in', $lifetime, '/', $cookieDomain ? $cookieDomain : null);
    }

    /**
     * Gets the domain to be used on setting cookies.
     * The information is taken from the value in $GLOBALS['TYPO3_CONF_VARS']['SYS']['cookieDomain'].
     *
     * @return string The domain to be used on setting cookies
     * @see AbstractUserAuthentication::getCookieDomain
     */
    protected static function getCookieDomain()
    {
        $result = '';
        $cookieDomain = $GLOBALS['TYPO3_CONF_VARS']['SYS']['cookieDomain'];
        // If a specific cookie domain is defined for a given TYPO3_MODE,
        // use that domain
        if (!empty($GLOBALS['TYPO3_CONF_VARS']['FE']['cookieDomain'])) {
            $cookieDomain = $GLOBALS['TYPO3_CONF_VARS']['FE']['cookieDomain'];
        }
        if ($cookieDomain) {
            if ($cookieDomain[0] == '/') {
                $match = [];
                $matchCnt = preg_match($cookieDomain, GeneralUtility::getIndpEnv('TYPO3_HOST_ONLY'), $match);
                if ($matchCnt === false) {
                    GeneralUtility::sysLog('The regular expression for the cookie domain (' . $cookieDomain . ') contains errors. The session is not shared across sub-domains.',
                        'Core', GeneralUtility::SYSLOG_SEVERITY_ERROR);
                } elseif ($matchCnt) {
                    $result = $match[0];
                }
            } else {
                $result = $cookieDomain;
            }
        }
        return $result;
    }
}

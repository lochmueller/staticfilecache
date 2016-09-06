<?php

/**
 * Logoff process
 */

namespace SFC\NcStaticfilecache\Hook;

use SFC\NcStaticfilecache\Utility\CookieUtility;
use TYPO3\CMS\Core\Authentication\AbstractUserAuthentication;

/**
 * LogoffFrontendUser
 */
class LogoffFrontendUser
{
    /**
     * Logoff process
     *
     * @param array $params
     * @param AbstractUserAuthentication $parent
     */
    public function logoff($params, AbstractUserAuthentication $parent)
    {
        if ($parent->loginType !== 'FE') {
            return;
        }

        CookieUtility::setCookie(1);
    }
}

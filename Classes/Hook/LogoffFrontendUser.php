<?php

/**
 * Logoff process
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Hook;

use SFC\Staticfilecache\Utility\CookieUtility;
use TYPO3\CMS\Core\Authentication\AbstractUserAuthentication;

/**
 * LogoffFrontendUser
 */
class LogoffFrontendUser extends AbstractHook
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

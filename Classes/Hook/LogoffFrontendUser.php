<?php

/**
 * Logoff process
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Hook;

use SFC\Staticfilecache\Service\CookieService;
use TYPO3\CMS\Core\Authentication\AbstractUserAuthentication;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
        GeneralUtility::makeInstance(CookieService::class)->setCookie(1);
    }
}

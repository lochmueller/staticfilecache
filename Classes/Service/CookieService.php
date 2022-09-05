<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Service;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Handle cookie related stuff.
 */
class CookieService extends AbstractService
{
    /**
     * The name of the cookie.
     */
    public const FE_COOKIE_NAME = 'staticfilecache';

    /**
     * Set the Cookie.
     *
     * @param int $lifetime
     */
    public function setCookie(int $lifetime = 0): void
    {
        setcookie(self::FE_COOKIE_NAME, 'typo_user_logged_in', $lifetime, '/', $this->getCookieDomain(), GeneralUtility::getIndpEnv('TYPO3_SSL'));
    }

    /**
     * Unset the Cookie.
     */
    public function unsetCookie(): void
    {
        $this->setCookie(-1);
    }

    public function hasCookie(): bool
    {
        return isset($_COOKIE[self::FE_COOKIE_NAME]);
    }

    /**
     * Gets the domain to be used on setting cookies.
     * The information is taken from the value in $GLOBALS['TYPO3_CONF_VARS']['SYS']['cookieDomain'].
     *
     * @return string The domain to be used on setting cookies or empty value
     *
     * @see AbstractUserAuthentication::getCookieDomain
     */
    protected function getCookieDomain(): string
    {
        $result = '';
        $cookieDomain = $GLOBALS['TYPO3_CONF_VARS']['SYS']['cookieDomain'];
        // If a specific cookie domain is defined; use that domain
        if (!empty($GLOBALS['TYPO3_CONF_VARS']['FE']['cookieDomain'])) {
            $cookieDomain = $GLOBALS['TYPO3_CONF_VARS']['FE']['cookieDomain'];
        }
        if ($cookieDomain) {
            if ('/' === $cookieDomain[0]) {
                $match = [];
                $matchCnt = preg_match($cookieDomain, GeneralUtility::getIndpEnv('TYPO3_HOST_ONLY'), $match);
                if (false === $matchCnt) {
                    $message = 'The regular expression for the cookie domain ('.$cookieDomain.') contains errors.';
                    $message .= 'The session is not shared across sub-domains.';
                    $this->logger->warning($message);
                } elseif ($matchCnt) {
                    $result = trim((string) $match[0]);
                }
            } else {
                $result = trim((string) $cookieDomain);
            }
        }

        return $result;
    }
}

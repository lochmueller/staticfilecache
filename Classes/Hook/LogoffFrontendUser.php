<?php

/**
 * Logoff process.
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Hook;

use SFC\Staticfilecache\Service\CookieService;
use TYPO3\CMS\Core\Authentication\AbstractUserAuthentication;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * LogoffFrontendUser.
 */
class LogoffFrontendUser extends AbstractHook
{
    /**
     * Logoff process.
     *
     * @param array $parameters
     */
    public function logoff($parameters, AbstractUserAuthentication $parentObject): void
    {
        $service = GeneralUtility::makeInstance(CookieService::class);
        if (('FE' === $parentObject->loginType || 'BE' === $parentObject->loginType) && $this->isNewSession($parentObject)) {
            $formData = $parentObject->getLoginFormData();
            if ('logout' !== $formData['status']) {
                $service->setCookie(time() + 3600);

                return;
            }
        }
        $service->unsetCookie();
    }

    private function isNewSession(AbstractUserAuthentication $parentObject): bool
    {
        if (10 === (new Typo3Version())->getMajorVersion()) {
            return true === $parentObject->newSessionID;
        }

        return $parentObject->getSession()->isNew();
    }
}

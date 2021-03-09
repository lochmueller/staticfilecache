<?php

/**
 * NoUserOrGroupSet.
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Cache\Rule;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * NoUserOrGroupSet.
 */
class NoUserOrGroupSet extends AbstractRule
{
    /**
     * Check if no user or group is set.
     */
    public function checkRule(ServerRequestInterface $request, array &$explanation, bool &$skipProcessing): void
    {
        if ($this->isUserOrGroupSet()) {
            $explanation[__CLASS__] = 'User or group are set';
        }
    }

    /**
     * Fix this bug: https://forge.typo3.org/issues/83212.
     *
     * @see TypoScriptFrontendController::isUserOrGroupSet
     *
     * @return bool TRUE if either a login user is found (array fe_user->user and valid id) OR if the gr_list is set to something else than '0,-1' (could be done even without a user being logged in!)
     */
    public function isUserOrGroupSet()
    {
        $context = GeneralUtility::makeInstance(Context::class);

        try {
            $userIsLoggedIn = (bool) $context->getPropertyFromAspect('frontend.user', 'isLoggedIn');
            $groupIds = (array) $context->getPropertyFromAspect('frontend.user', 'groupIds');
        } catch (AspectNotFoundException $e) {
            return false;
        }

        return $userIsLoggedIn || [0, -1] !== $groupIds;
    }
}

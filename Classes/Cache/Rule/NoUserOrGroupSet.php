<?php
/**
 * NoUserOrGroupSet.
 */
declare(strict_types=1);

namespace SFC\Staticfilecache\Cache\Rule;

use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * NoUserOrGroupSet.
 */
class NoUserOrGroupSet extends AbstractRule
{
    /**
     * Check if no user or group is set.
     *
     * @param TypoScriptFrontendController $frontendController
     * @param string                       $uri
     * @param array                        $explanation
     * @param bool                         $skipProcessing
     */
    public function checkRule(TypoScriptFrontendController $frontendController, string $uri, array &$explanation, bool &$skipProcessing)
    {
        if ($this->isUserOrGroupSet($frontendController)) {
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
    public function isUserOrGroupSet(TypoScriptFrontendController $frontendController)
    {
        return (\is_array($frontendController->fe_user->user) && isset($frontendController->fe_user->user['uid'])) || '0,-1' !== $frontendController->gr_list;
    }
}

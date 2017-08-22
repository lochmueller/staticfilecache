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
        if ($frontendController->isUserOrGroupSet()) {
            $explanation[__CLASS__] = 'User or group are set';
        }
    }
}

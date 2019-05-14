<?php

/**
 * No active BE user.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Cache\Rule;

use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * No active BE user.
 */
class NoBackendUser extends AbstractRule
{
    /**
     * No active BE user.
     *
     * @param TypoScriptFrontendController $frontendController
     * @param string                       $uri
     * @param array                        $explanation
     * @param bool                         $skipProcessing
     */
    public function checkRule(TypoScriptFrontendController $frontendController, string $uri, array &$explanation, bool &$skipProcessing)
    {
        if ($frontendController->isBackendUserLoggedIn()) {
            $skipProcessing = true;
            $explanation[__CLASS__] = 'Active BE Login (TSFE:beUserLogin)';
        }
    }
}

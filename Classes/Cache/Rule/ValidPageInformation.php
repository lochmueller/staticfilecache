<?php

/**
 * ValidPageInformation.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Cache\Rule;

use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * ValidPageInformation.
 *
 * @see https://github.com/lochmueller/staticfilecache/issues/150
 */
class ValidPageInformation extends AbstractRule
{
    /**
     * ValidPageInformation.
     *
     * @param TypoScriptFrontendController $frontendController
     * @param string                       $uri
     * @param array                        $explanation
     * @param bool                         $skipProcessing
     */
    public function checkRule(TypoScriptFrontendController $frontendController, string $uri, array &$explanation, bool &$skipProcessing)
    {
        if (!\is_array($frontendController->page) || !$pObj->page['uid']) {
            $skipProcessing = true;
            $explanation[__CLASS__] = 'There is no valid page in ths TSFE';
        }
    }
}

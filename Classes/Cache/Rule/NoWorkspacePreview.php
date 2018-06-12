<?php
/**
 * No workspace preview.
 */
declare(strict_types = 1);

namespace SFC\Staticfilecache\Cache\Rule;

use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * No workspace preview.
 */
class NoWorkspacePreview extends AbstractRule
{
    /**
     * Check if it is no workspace preview.
     *
     * @param TypoScriptFrontendController $frontendController
     * @param string                       $uri
     * @param array                        $explanation
     * @param bool                         $skipProcessing
     */
    public function checkRule(TypoScriptFrontendController $frontendController, string $uri, array &$explanation, bool &$skipProcessing)
    {
        if ($frontendController->doWorkspacePreview()) {
            $explanation[__CLASS__] = 'The page is in workspace preview mode';
        }
    }
}

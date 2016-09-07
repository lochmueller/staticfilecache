<?php
/**
 * No workspace preview
 *
 * @author  Tim Lochmüller
 */

namespace SFC\Staticfilecache\Cache\Rule;

use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * No workspace preview
 *
 * @author Tim Lochmüller
 */
class NoWorkspacePreview extends AbstractRule
{

    /**
     * Check if it is no workspace preview
     *
     * @param TypoScriptFrontendController $frontendController
     * @param string $uri
     * @param array $explanation
     * @param bool $skipProcessing
     */
    public function checkRule($frontendController, $uri, &$explanation, &$skipProcessing)
    {
        if ($frontendController->doWorkspacePreview()) {
            $explanation[__CLASS__] = 'The page is in workspace preview mode';
        }
    }
}

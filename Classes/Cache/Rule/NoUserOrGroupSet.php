<?php
/**
 * NoUserOrGroupSet
 *
 * @package SFC\NcStaticfilecache\Cache\Rule
 * @author  Tim Lochmüller
 */

namespace SFC\NcStaticfilecache\Cache\Rule;

use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * NoUserOrGroupSet
 *
 * @author Tim Lochmüller
 */
class NoUserOrGroupSet extends AbstractRule
{

    /**
     * Check if no user or group is set
     *
     * @param TypoScriptFrontendController $frontendController
     * @param string $uri
     * @param array $explanation
     * @param bool $skipProcessing
     *
     * @return array
     */
    public function checkRule($frontendController, $uri, &$explanation, &$skipProcessing)
    {
        if ($frontendController->isUserOrGroupSet()) {
            $explanation[__CLASS__] = 'User or group are set';
        }
    }
}

<?php
/**
 * Check if the doktype is valid
 *
 * @package SFC\NcStaticfilecache\Cache\Rule
 * @author  Tim Lochmüller
 */

namespace SFC\NcStaticfilecache\Cache\Rule;

use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Check if the doktype is valid
 *
 * @author Tim Lochmüller
 */
class ValidDoktype extends AbstractRule
{

    /**
     * Check if the URI is valid
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
        $ignoreTypes = [3];
        if (in_array($frontendController->page['doktype'], $ignoreTypes)) {
            $explanation[__CLASS__] = 'The Page doktype is one of the following not allowed numbers: ' . implode(', ',
                    $ignoreTypes);
            $skipProcessing = true;
        }
    }
}

<?php
/**
 * Check if the doktype is valid
 *
 */
declare(strict_types=1);

namespace SFC\Staticfilecache\Cache\Rule;

use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Check if the doktype is valid
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
     */
    public function checkRule(TypoScriptFrontendController $frontendController, string $uri, array &$explanation, bool &$skipProcessing)
    {
        $ignoreTypes = [3];
        if (in_array($frontendController->page['doktype'], $ignoreTypes)) {
            $explanation[__CLASS__] = 'The Page doktype is one of the following not allowed numbers: ' . implode(
                ', ',
                $ignoreTypes
            );
            $skipProcessing = true;
        }
    }
}

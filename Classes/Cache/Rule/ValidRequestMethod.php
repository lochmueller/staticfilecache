<?php

/**
 * ValidRequestMethod
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Cache\Rule;

use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * ValidRequestMethod
 */
class ValidRequestMethod extends AbstractRule
{

    /**
     * Method to check the rul and modify $explanation and/or $skipProcessing
     *
     * @param TypoScriptFrontendController $frontendController
     * @param string $uri
     * @param array $explanation
     * @param bool $skipProcessing
     */
    public function checkRule(TypoScriptFrontendController $frontendController, string $uri, array &$explanation, bool &$skipProcessing)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $explanation[__CLASS__] = 'The request methode has to be GET';
            $skipProcessing = true;
        }
    }
}

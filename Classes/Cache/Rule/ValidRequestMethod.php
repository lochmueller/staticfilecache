<?php

/**
 * ValidRequestMethod.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Cache\Rule;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * ValidRequestMethod.
 */
class ValidRequestMethod extends AbstractRule
{
    /**
     * Method to check the rul and modify $explanation and/or $skipProcessing.
     *
     * @param TypoScriptFrontendController $frontendController
     * @param ServerRequestInterface $request
     * @param array                        $explanation
     * @param bool                         $skipProcessing
     */
    public function checkRule(?TypoScriptFrontendController $frontendController, ServerRequestInterface $request, array &$explanation, bool &$skipProcessing)
    {
        if ('GET' !== $request->getMethod()) {
            $explanation[__CLASS__] = 'The request methode has to be GET';
            $skipProcessing = true;
        }
    }
}

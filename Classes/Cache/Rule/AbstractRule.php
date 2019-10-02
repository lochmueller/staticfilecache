<?php

/**
 * Abstract Rule.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Cache\Rule;

use Psr\Http\Message\ServerRequestInterface;
use SFC\Staticfilecache\StaticFileCacheObject;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Abstract Rule.
 */
abstract class AbstractRule extends StaticFileCacheObject
{

    /**
     * Method to check the rule and modify $explanation and/or $skipProcessing.
     *
     * @param TypoScriptFrontendController $frontendController
     * @param ServerRequestInterface                       $request
     * @param array                        $explanation
     * @param bool                         $skipProcessing
     */
    abstract public function checkRule(?TypoScriptFrontendController $frontendController, ServerRequestInterface $request, array &$explanation, bool &$skipProcessing);
}

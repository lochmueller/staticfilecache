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
     * Wrapper for the signal.
     *
     * @param TypoScriptFrontendController $frontendController
     * @param ServerRequestInterface $request
     * @param array                        $explanation
     * @param bool                         $skipProcessing
     *
     * @return array
     */
    public function check(TypoScriptFrontendController $frontendController, ServerRequestInterface $request, array $explanation, bool $skipProcessing): array
    {
        $this->checkRule($frontendController, $request, $explanation, $skipProcessing);

        return [
            'frontendController' => $frontendController,
            'request' => $request,
            'explanation' => $explanation,
            'skipProcessing' => $skipProcessing,
        ];
    }

    /**
     * Method to check the rul and modify $explanation and/or $skipProcessing.
     *
     * @param TypoScriptFrontendController $frontendController
     * @param ServerRequestInterface                       $request
     * @param array                        $explanation
     * @param bool                         $skipProcessing
     */
    abstract protected function checkRule(TypoScriptFrontendController $frontendController, ServerRequestInterface $request, array &$explanation, bool &$skipProcessing);
}

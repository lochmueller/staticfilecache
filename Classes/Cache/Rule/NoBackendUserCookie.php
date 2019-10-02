<?php

/**
 * No active BE user (just check the cookie).
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Cache\Rule;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * No active BE user (just check the cookie).
 */
class NoBackendUserCookie extends AbstractRule
{
    /**
     * No active BE user cookie.
     *
     * @param TypoScriptFrontendController $frontendController
     * @param ServerRequestInterface $request
     * @param array $explanation
     * @param bool $skipProcessing
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     */
    public function checkRule(?TypoScriptFrontendController $frontendController, ServerRequestInterface $request, array &$explanation, bool &$skipProcessing)
    {
        if (isset($_COOKIE[$GLOBALS['TYPO3_CONF_VARS']['BE']['cookieName']])) {
            $skipProcessing = true;
            $explanation[__CLASS__] = 'BE Login Cookie';
        }
    }
}

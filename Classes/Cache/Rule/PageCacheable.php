<?php

/**
 * Check if the current page is static cacheable in Page property context.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Cache\Rule;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Check if the current page is static cacheable in Page property context.
 */
class PageCacheable extends AbstractRule
{
    /**
     * Check if the current page is static cacheable in Page property context.
     *
     * @param TypoScriptFrontendController $frontendController
     * @param ServerRequestInterface $request
     * @param array                        $explanation
     * @param bool                         $skipProcessing
     */
    public function checkRule(?TypoScriptFrontendController $frontendController, ServerRequestInterface $request, array &$explanation, bool &$skipProcessing)
    {
        $cache = (bool)($frontendController->page['tx_staticfilecache_cache'] ?? true);
        if (!$cache) {
            $explanation[__CLASS__] = 'static cache disabled on page';
        }
    }
}

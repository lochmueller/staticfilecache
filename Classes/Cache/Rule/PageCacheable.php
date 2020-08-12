<?php

/**
 * Check if the current page is static cacheable in Page property context.
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Cache\Rule;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Check if the current page is static cacheable in Page property context.
 */
class PageCacheable extends AbstractRule
{
    /**
     * Check if the current page is static cacheable in Page property context.
     *
     *
     * @param ServerRequestInterface $request
     * @param array                        $explanation
     * @param bool                         $skipProcessing
     */
    public function checkRule(ServerRequestInterface $request, array &$explanation, bool &$skipProcessing): void
    {
        $cache = (bool)($GLOBALS['TSFE']->page['tx_staticfilecache_cache'] ?? true);
        if (!$cache) {
            $explanation[__CLASS__] = 'static cache disabled on page';
        }
    }
}

<?php

/**
 * No no_cache.
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Cache\Rule;

use Psr\Http\Message\ServerRequestInterface;

/**
 * No no_cache.
 */
class NoNoCache extends AbstractRule
{
    /**
     * No no_cache.
     *
     *
     * @param ServerRequestInterface $request
     * @param array                        $explanation
     * @param bool                         $skipProcessing
     */
    public function checkRule(ServerRequestInterface $request, array &$explanation, bool &$skipProcessing): void
    {
        if ($GLOBALS['TSFE']->no_cache) {
            $explanation[__CLASS__] = 'config.no_cache is true';
        }
    }
}

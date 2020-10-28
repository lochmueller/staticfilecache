<?php

/**
 * NoCrawlerCall.
 */

namespace SFC\Staticfilecache\Cache\Rule;

use Psr\Http\Message\ServerRequestInterface;

class NoCrawlerCall extends AbstractRule
{
    public function checkRule(ServerRequestInterface $request, array &$explanation, bool &$skipProcessing): void
    {
        $crawlerInformation = $request->getHeaderLine('X-T3CRAWLER') ?? null;
        if (!empty($crawlerInformation)) {
            // @todo find another solution to cache content via Crawler (avoid caching the status JSON)
            // @see https://github.com/lochmueller/staticfilecache/issues/260
            $explanation[__CLASS__] = 'It is a Crawler configuration call, that should not cached by SFC';
            $skipProcessing = true;
        }
    }
}

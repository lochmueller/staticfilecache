<?php

/**
 * NoCrawlerCall
 */
namespace SFC\Staticfilecache\Cache\Rule;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class NoCrawlerCall extends AbstractRule
{
    public function checkRule(?TypoScriptFrontendController $frontendController, ServerRequestInterface $request, array &$explanation, bool &$skipProcessing)
    {
        $crawlerInformation = $request->getHeaderLine('X-T3CRAWLER') ?? null;
        if (!empty($crawlerInformation)) {
            // @todo find another solution to cache content via Crawler (avoid caching the status JSON)
            $explanation[__CLASS__] = 'It is a Crawler configuration call, that should not cached by SFC';
            $skipProcessing = true;
        }
    }
}

<?php

/**
 * Check if the current page is static cacheable in TSFE context.
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Cache\Rule;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Check if the current page is static cacheable in TSFE context.
 */
class StaticCacheable extends AbstractRule
{
    /**
     * Check if the page is static cacheable.
     *
     * Please keep this topic in mind: https://forge.typo3.org/issues/83212
     * EXT:form honeypot uses anonymous FE user, so the caching is disabled
     */
    public function checkRule(ServerRequestInterface $request, array &$explanation, bool &$skipProcessing): void
    {
        if (\is_object($GLOBALS['TSFE']) && !$GLOBALS['TSFE']->isStaticCacheble()) {
            $explanation[__CLASS__] = 'The page is not static cacheable via TypoScriptFrontend. Check the first Question on: https://github.com/lochmueller/staticfilecache/blob/master/Documentation/Faq/Index.rst';
        }
    }
}

<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Cache\Listener;

use SFC\Staticfilecache\Event\CacheRuleEvent;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Check if the current page is static cacheable in TSFE context.
 */
class StaticCacheableListener
{
    /**
     * Check if the page is static cacheable.
     *
     * Please keep this topic in mind: https://forge.typo3.org/issues/83212
     * EXT:form honeypot uses anonymous FE user, so the caching is disabled
     */
    public function __invoke(CacheRuleEvent $event): void
    {
        if (($GLOBALS['TSFE'] ?? null) instanceof TypoScriptFrontendController && !$GLOBALS['TSFE']->isStaticCacheble()) {
            $event->addExplanation(__CLASS__, 'The page is not static cacheable via TypoScriptFrontend. Check the first Question on: https://github.com/lochmueller/staticfilecache/blob/master/Documentation/Faq/Index.rst');
        }
    }
}

<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Cache\Listener;

use SFC\Staticfilecache\Event\CacheRuleEvent;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Check if the current page is static cacheable in TSFE context.
 */
class StaticCacheableListener
{
    public function __construct(private readonly Typo3Version $typo3Version) {}

    /**
     * Check if the page is static cacheable.
     *
     * Please keep this topic in mind: https://forge.typo3.org/issues/83212
     * EXT:form honeypot uses anonymous FE user, so the caching is disabled
     */
    public function __invoke(CacheRuleEvent $event): void
    {
        // @todo migrate this event to check
        // $request->getAttribute('frontend.cache.instruction')->isCachingAllowed()
        if (($GLOBALS['TSFE'] ?? null) instanceof TypoScriptFrontendController) {
            if ($this->typo3Version->getMajorVersion() >= 13) {
                /* @phpstan-ignore-next-line */
                $isStaticCacheble = $GLOBALS['TSFE']->isStaticCacheble($event->getRequest());
            } else {
                /* @phpstan-ignore-next-line */
                $isStaticCacheble = $GLOBALS['TSFE']->isStaticCacheble();
            }
            if (!$isStaticCacheble) {
                $event->addExplanation(__CLASS__, 'The page is not static cacheable via TypoScriptFrontend. Check the first Question on: https://github.com/lochmueller/staticfilecache/blob/master/Documentation/Faq/Index.rst');
            }
        }
    }
}

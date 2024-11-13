<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Cache\Listener;

use SFC\Staticfilecache\Event\CacheRuleEvent;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class CachingAllowedListener
{
    public function __construct(private readonly Typo3Version $typo3Version) {}

    public function __invoke(CacheRuleEvent $event): void
    {
        if ($this->typo3Version->getMajorVersion() >= 13) {
            if (!$event->getRequest()->getAttribute('frontend.cache.instruction')->isCachingAllowed()) {
                $event->addExplanation(__CLASS__, 'No caching via frontend.cache.instruction attribute');
            }
            return;
        }

        // v12
        $tsfe = $GLOBALS['TSFE'] ?? null;
        /* @phpstan-ignore-next-line */
        if ($tsfe instanceof TypoScriptFrontendController && $tsfe->no_cache) {
            $event->addExplanation(__CLASS__, 'config.no_cache is true');
        }
    }
}

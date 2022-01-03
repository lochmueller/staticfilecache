<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Cache\Listener;

use SFC\Staticfilecache\Event\CacheRuleEvent;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class PageCacheableListener
{
    public function __invoke(CacheRuleEvent $event): void
    {
        if (!(($GLOBALS['TSFE'] ?? null) instanceof TypoScriptFrontendController)) {
            return;
        }
        $cache = (bool) ($GLOBALS['TSFE']->page['tx_staticfilecache_cache'] ?? true);
        if (!$cache) {
            $event->addExplanation(__CLASS__, 'static cache disabled on page');
        }
    }
}

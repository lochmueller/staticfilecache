<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\EventListener\CacheRule;

use SFC\Staticfilecache\Event\CacheRuleEvent;

class PageCacheableListener
{
    public function __invoke(CacheRuleEvent $event): void
    {
        $cache = (bool)($GLOBALS['TSFE']->page['tx_staticfilecache_cache'] ?? true);
        if (!$cache) {
            $event->addExplanation(__CLASS__, 'static cache disabled on page');
        }
    }
}

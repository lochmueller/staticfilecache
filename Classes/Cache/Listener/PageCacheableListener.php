<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Cache\Listener;

use SFC\Staticfilecache\Event\CacheRuleEvent;
use TYPO3\CMS\Frontend\Page\PageInformation;

class PageCacheableListener
{
    public function __invoke(CacheRuleEvent $event): void
    {
        $pageInformation = $event->getRequest()->getAttribute('frontend.page.information');
        if (!$pageInformation instanceof PageInformation) {
            return;
        }

        $cache = (bool) ($pageInformation->getPageRecord()['tx_staticfilecache_cache'] ?? true);
        if (!$cache) {
            $event->addExplanation(__CLASS__, 'static cache disabled on page');
        }
    }
}

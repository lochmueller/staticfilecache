<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Cache\Listener;

use SFC\Staticfilecache\Event\CacheRuleEvent;
use TYPO3\CMS\Frontend\Cache\CacheInstruction;

class CachingAllowedListener
{
    public function __invoke(CacheRuleEvent $event): void
    {
        if (!$event->getRequest()->getAttribute('frontend.cache.instruction', new CacheInstruction())->isCachingAllowed()) {
            $event->addExplanation(__CLASS__, 'No caching via frontend.cache.instruction attribute');
        }
    }
}

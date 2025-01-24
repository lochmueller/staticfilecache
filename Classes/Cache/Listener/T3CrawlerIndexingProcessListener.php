<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Cache\Listener;

use SFC\Staticfilecache\Event\CacheRuleFallbackEvent;

/**
 * T3Crawler Indexing process.
 */
class T3CrawlerIndexingProcessListener
{
    public function __invoke(CacheRuleFallbackEvent $event): void
    {
        if ($event->getRequest()->hasHeader('X-T3Crawler')) {
            $event->addExplanation(__CLASS__, 'T3Crawler Indexing request');
            $event->setSkipProcessing(true);
        }
    }
}

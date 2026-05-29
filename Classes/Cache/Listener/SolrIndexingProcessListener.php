<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Cache\Listener;

use SFC\Staticfilecache\Event\CacheRuleFallbackEvent;

/**
 * Solr Indexing process.
 */
class SolrIndexingProcessListener
{
    public function __invoke(CacheRuleFallbackEvent $event): void
    {
        // Note!! This is handled by old EXT:solr versions!!!
        // The new version set the 'frontend.cache.instruction' in the
        // right way and is cached via CachingAllowedListener.

        if ($event->getRequest()->hasHeader('X-Tx-Solr-Iq')) {
            $event->addExplanation(__CLASS__, 'Solr Indexing request');
            $event->setSkipProcessing(true);
        }
    }
}

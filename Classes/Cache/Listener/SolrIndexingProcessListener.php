<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Cache\Listener;

use SFC\Staticfilecache\Event\CacheRuleEventInterface;

/**
 * Solr Indexing process.
 */
class SolrIndexingProcessListener
{
    public function __invoke(CacheRuleEventInterface $event): void
    {
        if ($event->getRequest()->getAttribute('solr.indexingInstructions', null) !== null) {
            $event->addExplanation(__CLASS__, 'Solr Indexing request');
            $event->setSkipProcessing(true);
        }
    }
}

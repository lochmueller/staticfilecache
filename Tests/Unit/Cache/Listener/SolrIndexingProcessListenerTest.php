<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Cache\Listener;

use SFC\Staticfilecache\Cache\Listener\SolrIndexingProcessListener;
use TYPO3\CMS\Core\Http\ServerRequest;

class SolrIndexingProcessListenerTest extends AbstractListenerTest
{
    public function testNoExplanation(): void
    {
        $listener = new SolrIndexingProcessListener();

        $event = $this->emptyCacheRuleEvent();
        $listener($event);

        self::assertEquals([], $event->getExplanation());
        self::assertEquals(false, $event->isSkipProcessing());
    }

    public function testExplanationAndSkip(): void
    {
        $listener = new SolrIndexingProcessListener();

        $event = $this->cacheRuleEvent((new ServerRequest())->withAttribute('solr.indexingInstructions', new \stdClass()));
        $listener($event);

        self::assertArrayHasKey(SolrIndexingProcessListener::class, $event->getExplanation());
        self::assertEquals(true, $event->isSkipProcessing());
    }
}

<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Cache\Listener;

use SFC\Staticfilecache\Cache\Listener\T3CrawlerIndexingProcessListener;
use SFC\Staticfilecache\Event\CacheRuleFallbackEvent;
use TYPO3\CMS\Core\Http\ServerRequest;

class T3CrawlerIndexingProcessListenerTest extends AbstractListenerTest
{
    public function testNoExplanation(): void
    {
        $listener = new T3CrawlerIndexingProcessListener();

        $event = new CacheRuleFallbackEvent(new ServerRequest(), [], false);
        $listener($event);

        self::assertEquals([], $event->getExplanation());
        self::assertEquals(false, $event->isSkipProcessing());
    }

    public function testExplanationAndSkip(): void
    {
        $listener = new T3CrawlerIndexingProcessListener();

        $event = new CacheRuleFallbackEvent((new ServerRequest())->withHeader('X-T3Crawler', '1'), [], false);
        $listener($event);

        self::assertArrayHasKey(T3CrawlerIndexingProcessListener::class, $event->getExplanation());
        self::assertEquals(true, $event->isSkipProcessing());
    }
}

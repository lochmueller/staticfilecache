<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Cache\Listener;

use SFC\Staticfilecache\Cache\Listener\NoLongPathSegmentListener;
use TYPO3\CMS\Core\Http\ServerRequest;

class NoLongPathSegmentListenerTest extends AbstractListenerTest
{
    public function testNoExplanation(): void
    {
        $listener = new NoLongPathSegmentListener();

        $event = $this->cacheRuleEvent(new ServerRequest('https://example.com/' . str_repeat('a', 255) . '/page/'));
        $listener($event);

        self::assertEquals([], $event->getExplanation());
        self::assertEquals(false, $event->isSkipProcessing());
    }

    public function testExplanationAndSkip(): void
    {
        $listener = new NoLongPathSegmentListener();

        $event = $this->cacheRuleEvent(new ServerRequest('https://example.com/page/' . str_repeat('a', 256) . '/'));
        $listener($event);

        self::assertArrayHasKey(NoLongPathSegmentListener::class, $event->getExplanation());
        self::assertEquals(true, $event->isSkipProcessing());
    }
}

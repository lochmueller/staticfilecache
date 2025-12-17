<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Cache\Listener;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SFC\Staticfilecache\Cache\Listener\NoAuthorizationListener;
use SFC\Staticfilecache\Event\CacheRuleEvent;

class NoAuthorizationListenerTest extends AbstractListenerTest
{
    public function testNoExplanation(): void
    {
        $listener = new NoAuthorizationListener();

        $event = $this->emptyCacheRuleEvent();
        $listener($event);

        self::assertEquals([], $event->getExplanation());
        self::assertEquals(false, $event->isSkipProcessing());
    }


    public function testAddExplanation(): void
    {
        $listener = new NoAuthorizationListener();

        $request = $this->getMockBuilder(ServerRequestInterface::class)->getMock();
        $request->method('getHeaderLine')->willReturn('Content');

        $event = new CacheRuleEvent(
            $request,
            [],
            false,
            $this->getMockBuilder(ResponseInterface::class)->getMock(),
        );
        $listener($event);

        self::assertNotEquals([], $event->getExplanation());
        self::assertEquals(true, $event->isSkipProcessing());
    }
}

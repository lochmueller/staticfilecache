<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Cache\Listener;

use Psr\Http\Message\ServerRequestInterface;
use SFC\Staticfilecache\Cache\Listener\NoBackendUserListener;
use SFC\Staticfilecache\Cache\Listener\ValidRequestMethodListener;
use SFC\Staticfilecache\Event\CacheRuleEvent;
use SFC\Staticfilecache\Event\CacheRuleEventInterface;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\UserAspect;

class ValidRequestMethodListenerTest extends AbstractListenerTest
{
    public function testNoExplanation(): void
    {

        $listener = new ValidRequestMethodListener();

        $request = $this->getMockBuilder(ServerRequestInterface::class)->getMock();
        $request->method('getMethod')->willReturn('GET');

        $event = new CacheRuleEvent(
            $request,
            [],
            false
        );
        $listener($event);

        self::assertEquals([], $event->getExplanation());
        self::assertEquals(false, $event->isSkipProcessing());
    }

    public function testExplanationAndSkip(): void
    {
        $listener = new ValidRequestMethodListener();

        $request = $this->getMockBuilder(ServerRequestInterface::class)->getMock();
        $request->method('getMethod')->willReturn('POST');

        $event = new CacheRuleEvent(
            $request,
            [],
            false
        );
        $listener($event);

        self::assertNotEquals([], $event->getExplanation());
        self::assertEquals(true, $event->isSkipProcessing());
    }

}

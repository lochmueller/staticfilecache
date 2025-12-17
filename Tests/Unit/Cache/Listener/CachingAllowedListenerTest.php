<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Cache\Listener;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SFC\Staticfilecache\Cache\Listener\CachingAllowedListener;
use SFC\Staticfilecache\Event\CacheRuleEvent;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Extbase\Mvc\Request;

class CachingAllowedListenerTest extends AbstractListenerTest
{
    public function testNoExplanation(): void
    {
        $listener = new CachingAllowedListener();

        $event = $this->emptyCacheRuleEvent();
        $listener($event);

        self::assertEmpty($event->getExplanation());
        self::assertEquals(false, $event->isSkipProcessing());

    }
    protected function emptyCacheRuleEvent(): CacheRuleEvent
    {
        return new CacheRuleEvent(
            new ServerRequest(),
            [],
            false,
            $this->getMockBuilder(ResponseInterface::class)->getMock(),
        );
    }
}

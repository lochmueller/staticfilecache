<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Cache\Listener;

use Psr\Http\Message\ServerRequestInterface;
use SFC\Staticfilecache\Cache\Listener\CachingAllowedListener;
use SFC\Staticfilecache\Event\CacheRuleEvent;
use TYPO3\CMS\Core\Information\Typo3Version;

class CachingAllowedListenerTest extends AbstractListenerTest
{
    public function testNoExplanation(): void
    {
        $version = $this->getMockBuilder(Typo3Version::class)->disableOriginalConstructor()->getMock();
        $version->method('getMajorVersion')->willReturn(12);

        $listener = new CachingAllowedListener($version);

        $event = $this->emptyCacheRuleEvent();
        $listener($event);

        self::assertEquals([], $event->getExplanation());
        self::assertEquals(false, $event->isSkipProcessing());

    }
    protected function emptyCacheRuleEvent(): CacheRuleEvent
    {
        return new CacheRuleEvent(
            $this->getMockBuilder(ServerRequestInterface::class)->getMock(),
            [],
            false
        );
    }
}

<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Cache\Listener;

use Psr\Http\Message\ServerRequestInterface;
use SFC\Staticfilecache\Cache\Listener\ForceStaticCacheListener;
use SFC\Staticfilecache\Event\CacheRuleEvent;
use TYPO3\CMS\Core\EventDispatcher\NoopEventDispatcher;

class ForceStaticCacheListenerTest extends AbstractListenerTest
{
    public function testNoExplanation(): void
    {
        $listener = new ForceStaticCacheListener(new NoopEventDispatcher());

        $cacheRuleEvent = new CacheRuleEvent(
            $this->getMockBuilder(ServerRequestInterface::class)->getMock(),
            ['dummy'],
            false
        );

        $listener($cacheRuleEvent);

        self::assertEquals(['dummy'], $cacheRuleEvent->getExplanation());
        self::assertEquals(false, $cacheRuleEvent->isSkipProcessing());

    }

}

<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Cache\Listener;

use SFC\Staticfilecache\Cache\Listener\CachingAllowedListener;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Frontend\Cache\CacheInstruction;

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

    public function testNoExplanationWithAllowedCacheInstruction(): void
    {
        $listener = new CachingAllowedListener();

        $event = $this->cacheRuleEvent((new ServerRequest())->withAttribute('frontend.cache.instruction', new CacheInstruction()));
        $listener($event);

        self::assertEmpty($event->getExplanation());
    }

    public function testExplanationWithDisabledCacheInstruction(): void
    {
        $cacheInstruction = new CacheInstruction();
        $cacheInstruction->disableCache('Test');

        $listener = new CachingAllowedListener();

        $event = $this->cacheRuleEvent((new ServerRequest())->withAttribute('frontend.cache.instruction', $cacheInstruction));
        $listener($event);

        self::assertArrayHasKey(CachingAllowedListener::class, $event->getExplanation());
        self::assertEquals(false, $event->isSkipProcessing());
    }
}

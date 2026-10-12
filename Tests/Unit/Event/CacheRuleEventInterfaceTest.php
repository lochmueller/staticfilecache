<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Event;

use SFC\Staticfilecache\Event\CacheRuleEvent;
use SFC\Staticfilecache\Event\CacheRuleEventInterface;
use SFC\Staticfilecache\Event\CacheRuleFallbackEvent;
use SFC\Staticfilecache\Tests\Unit\AbstractTest;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\ServerRequest;

/**
 * CacheRuleEventInterfaceTest.
 *
 * @internal
 * @coversNothing
 */
final class CacheRuleEventInterfaceTest extends AbstractTest
{
    public function testCacheRuleEventsImplementInterface(): void
    {
        $request = new ServerRequest('https://example.com/');

        self::assertInstanceOf(CacheRuleEventInterface::class, new CacheRuleEvent($request, [], false, new Response()));
        self::assertInstanceOf(CacheRuleEventInterface::class, new CacheRuleFallbackEvent($request, [], false));
    }

    public function testInterfaceCanBeUsedForSharedListenerLogic(): void
    {
        $events = [
            new CacheRuleEvent(new ServerRequest('https://example.com/'), [], false, new Response()),
            new CacheRuleFallbackEvent(new ServerRequest('https://example.com/'), [], false),
        ];

        foreach ($events as $event) {
            $this->markAsSkipped($event);
            self::assertTrue($event->isSkipProcessing());
            self::assertSame(['test' => 'skipped'], $event->getExplanation());
        }
    }

    private function markAsSkipped(CacheRuleEventInterface $event): void
    {
        $event->addExplanation('test', 'skipped');
        $event->setSkipProcessing(true);
    }
}

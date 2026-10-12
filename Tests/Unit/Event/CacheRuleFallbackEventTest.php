<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Event;

use SFC\Staticfilecache\Event\CacheRuleFallbackEvent;
use SFC\Staticfilecache\Tests\Unit\AbstractTest;
use TYPO3\CMS\Core\Http\ServerRequest;

/**
 * CacheRuleFallbackEventTest.
 *
 * @internal
 * @coversNothing
 */
final class CacheRuleFallbackEventTest extends AbstractTest
{
    public function testGetRequest(): void
    {
        $request = new ServerRequest('https://example.com/');
        $event = new CacheRuleFallbackEvent($request, [], false);

        self::assertSame($request, $event->getRequest());
    }

    public function testExplanationHandling(): void
    {
        $event = new CacheRuleFallbackEvent(new ServerRequest('https://example.com/'), ['first' => 'one'], false);

        $event->addExplanation('second', 'two');
        self::assertSame(['first' => 'one', 'second' => 'two'], $event->getExplanation());

        $event->truncateExplanations();
        self::assertSame([], $event->getExplanation());
    }

    public function testSkipProcessing(): void
    {
        $event = new CacheRuleFallbackEvent(new ServerRequest('https://example.com/'), [], true);
        self::assertTrue($event->isSkipProcessing());

        $event->setSkipProcessing(false);
        self::assertFalse($event->isSkipProcessing());
    }
}

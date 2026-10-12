<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Event;

use SFC\Staticfilecache\Event\CacheRuleEvent;
use SFC\Staticfilecache\Tests\Unit\AbstractTest;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\ServerRequest;

/**
 * CacheRuleEventTest.
 *
 * @internal
 * @coversNothing
 */
final class CacheRuleEventTest extends AbstractTest
{
    public function testGetRequestAndResponse(): void
    {
        $request = new ServerRequest('https://example.com/');
        $response = new Response();
        $event = new CacheRuleEvent($request, [], false, $response);

        self::assertSame($request, $event->getRequest());
        self::assertSame($response, $event->getResponse());
    }

    public function testExplanationHandling(): void
    {
        $event = new CacheRuleEvent(new ServerRequest('https://example.com/'), ['first' => 'one'], false, new Response());

        $event->addExplanation('second', 'two');
        self::assertSame(['first' => 'one', 'second' => 'two'], $event->getExplanation());

        $event->truncateExplanations();
        self::assertSame([], $event->getExplanation());
    }

    public function testSkipProcessing(): void
    {
        $event = new CacheRuleEvent(new ServerRequest('https://example.com/'), [], false, new Response());
        self::assertFalse($event->isSkipProcessing());

        $event->setSkipProcessing(true);
        self::assertTrue($event->isSkipProcessing());
    }
}

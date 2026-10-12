<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Event;

use SFC\Staticfilecache\Event\PreGenerateEvent;
use SFC\Staticfilecache\Tests\Unit\AbstractTest;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\ServerRequest;

/**
 * PreGenerateEventTest.
 *
 * @internal
 * @coversNothing
 */
final class PreGenerateEventTest extends AbstractTest
{
    public function testGetter(): void
    {
        $request = new ServerRequest('https://example.com/');
        $response = new Response();
        $event = new PreGenerateEvent('https://example.com/', $request, $response);

        self::assertSame('https://example.com/', $event->getUri());
        self::assertSame($request, $event->getRequest());
        self::assertSame($response, $event->getResponse());
    }

    public function testSetUriAndResponse(): void
    {
        $event = new PreGenerateEvent('https://example.com/', new ServerRequest('https://example.com/'), new Response());
        $response = new Response(null, 404);

        $event->setUri('https://example.com/other/');
        $event->setResponse($response);

        self::assertSame('https://example.com/other/', $event->getUri());
        self::assertSame($response, $event->getResponse());
    }
}

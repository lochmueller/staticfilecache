<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Event;

use SFC\Staticfilecache\Event\HttpPushHeaderEvent;
use SFC\Staticfilecache\Tests\Unit\AbstractTest;
use TYPO3\CMS\Core\Http\ServerRequest;

/**
 * HttpPushHeaderEventTest.
 *
 * @internal
 * @coversNothing
 */
final class HttpPushHeaderEventTest extends AbstractTest
{
    public function testGetter(): void
    {
        $request = new ServerRequest('https://example.com/');
        $event = new HttpPushHeaderEvent([['path' => '/main.css', 'type' => 'style']], '<html></html>', ['css', 'js'], $request);

        self::assertSame([['path' => '/main.css', 'type' => 'style']], $event->getHeaders());
        self::assertSame('<html></html>', $event->getContent());
        self::assertSame(['css', 'js'], $event->getExtensions());
        self::assertSame($request, $event->getRequest());
    }

    public function testSetHeaders(): void
    {
        $event = new HttpPushHeaderEvent([], '', [], new ServerRequest('https://example.com/'));
        $event->setHeaders([['path' => '/main.js', 'type' => 'script']]);

        self::assertSame([['path' => '/main.js', 'type' => 'script']], $event->getHeaders());
    }
}

<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Service\HttpPush;

use SFC\Staticfilecache\Event\HttpPushHeaderEvent;
use SFC\Staticfilecache\Service\HttpPush\SvgHttpPush;

/**
 * SvgHttpPushTest.
 *
 * @internal
 * @coversNothing
 */
final class SvgHttpPushTest extends AbstractHttpPushTest
{
    public function testCanHandleExtension(): void
    {
        $service = new SvgHttpPush();

        self::assertTrue($service->canHandleExtension('svg'));
        self::assertFalse($service->canHandleExtension('png'));
    }

    public function testGetValidHeaders(): void
    {
        $headers = (new SvgHttpPush())->getHeaders('<img src="/icons/logo.svg"><use href="/icons/sprite.svg#home"/><img src="https://cdn.example.org/external.svg">', $this->getRequest());

        self::assertSame([
            ['path' => '/icons/logo.svg', 'type' => 'image'],
            ['path' => '/icons/sprite.svg', 'type' => 'image'],
        ], $headers);
    }

    public function testGetNoHeadersWithoutMatches(): void
    {
        self::assertSame([], (new SvgHttpPush())->getHeaders('<html></html>', $this->getRequest()));
    }

    public function testInvokeAddHeadersForHandledExtensions(): void
    {
        $event = new HttpPushHeaderEvent([['path' => '/existing.css', 'type' => 'style']], '<img src="/logo.svg">', ['css', 'svg'], $this->getRequest());

        (new SvgHttpPush())($event);

        self::assertSame([
            ['path' => '/existing.css', 'type' => 'style'],
            ['path' => '/logo.svg', 'type' => 'image'],
        ], $event->getHeaders());
    }

    public function testInvokeIgnoreNotHandledExtensions(): void
    {
        $event = new HttpPushHeaderEvent([], '<img src="/logo.svg">', ['css'], $this->getRequest());

        (new SvgHttpPush())($event);

        self::assertSame([], $event->getHeaders());
    }
}

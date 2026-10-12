<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Service\HttpPush;

use SFC\Staticfilecache\Service\HttpPush\FontHttpPush;

/**
 * FontHttpPushTest.
 *
 * @internal
 * @coversNothing
 */
final class FontHttpPushTest extends AbstractHttpPushTest
{
    public function testCanHandleExtension(): void
    {
        $service = new FontHttpPush();

        self::assertTrue($service->canHandleExtension('woff'));
        self::assertTrue($service->canHandleExtension('woff2'));
        self::assertFalse($service->canHandleExtension('ttf'));
    }

    public function testGetNoHeadersWithoutHandledExtension(): void
    {
        self::assertSame([], (new FontHttpPush())->getHeaders('<link href="/font.woff2">', $this->getRequest()));
    }

    public function testGetValidHeaders(): void
    {
        $service = new FontHttpPush();
        $service->canHandleExtension('woff2');

        $headers = $service->getHeaders('<link href="/fonts/a.woff2"><link href="/fonts/a.woff2"><link href="/fonts/b.woff">', $this->getRequest());

        self::assertSame([['path' => '/fonts/a.woff2', 'type' => 'font']], $headers);
    }

    public function testGetNoHeadersWithoutMatches(): void
    {
        $service = new FontHttpPush();
        $service->canHandleExtension('woff');

        self::assertSame([], $service->getHeaders($this->getExampleContent(), $this->getRequest()));
    }
}

<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Event;

use SFC\Staticfilecache\Event\BuildClientEvent;
use SFC\Staticfilecache\Tests\Unit\AbstractTest;

/**
 * BuildClientEventTest.
 *
 * @internal
 * @coversNothing
 */
final class BuildClientEventTest extends AbstractTest
{
    public function testGetAndSetOptions(): void
    {
        $event = new BuildClientEvent(['base_uri' => 'https://example.com/'], ['timeout' => 5]);

        self::assertSame(['base_uri' => 'https://example.com/'], $event->getOptions());
        self::assertSame(['timeout' => 5], $event->getHttpOptions());

        $event->setOptions(['verify' => false]);
        $event->setHttpOptions(['timeout' => 10]);

        self::assertSame(['verify' => false], $event->getOptions());
        self::assertSame(['timeout' => 10], $event->getHttpOptions());
    }
}

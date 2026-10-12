<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Event;

use SFC\Staticfilecache\Event\BuildIdentifierEvent;
use SFC\Staticfilecache\Tests\Unit\AbstractTest;

/**
 * BuildIdentifierEventTest.
 *
 * @internal
 * @coversNothing
 */
final class BuildIdentifierEventTest extends AbstractTest
{
    public function testGetAndSetParts(): void
    {
        $event = new BuildIdentifierEvent('https://example.com/page/', ['path' => 'page', 'index' => 'index']);

        self::assertSame('https://example.com/page/', $event->getRequestUri());
        self::assertSame(['path' => 'page', 'index' => 'index'], $event->getParts());

        $event->setParts(['path' => 'other']);

        self::assertSame(['path' => 'other'], $event->getParts());
    }
}

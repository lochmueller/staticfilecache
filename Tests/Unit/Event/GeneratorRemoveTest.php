<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Event;

use SFC\Staticfilecache\Event\GeneratorRemove;
use SFC\Staticfilecache\Tests\Unit\AbstractTest;

/**
 * GeneratorRemoveTest.
 *
 * @internal
 * @coversNothing
 */
final class GeneratorRemoveTest extends AbstractTest
{
    public function testGetter(): void
    {
        $event = new GeneratorRemove('https://example.com/', '/var/www/cache/index');

        self::assertSame('https://example.com/', $event->getEntryIdentifier());
        self::assertSame('/var/www/cache/index', $event->getFileName());
    }
}

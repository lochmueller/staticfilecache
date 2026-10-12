<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Event;

use SFC\Staticfilecache\Event\GeneratorCreate;
use SFC\Staticfilecache\Tests\Unit\AbstractTest;
use TYPO3\CMS\Core\Http\Response;

/**
 * GeneratorCreateTest.
 *
 * @internal
 * @coversNothing
 */
final class GeneratorCreateTest extends AbstractTest
{
    public function testGetter(): void
    {
        $response = new Response();
        $event = new GeneratorCreate('https://example.com/', '/var/www/cache/index', $response, 3600);

        self::assertSame('https://example.com/', $event->getEntryIdentifier());
        self::assertSame('/var/www/cache/index', $event->getFileName());
        self::assertSame($response, $event->getResponse());
        self::assertSame(3600, $event->getLifetime());
    }
}

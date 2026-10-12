<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Event;

use SFC\Staticfilecache\Event\GeneratorContentManipulationEvent;
use SFC\Staticfilecache\Tests\Unit\AbstractTest;

/**
 * GeneratorContentManipulationEventTest.
 *
 * @internal
 * @coversNothing
 */
final class GeneratorContentManipulationEventTest extends AbstractTest
{
    public function testGetAndSetContent(): void
    {
        $event = new GeneratorContentManipulationEvent('<html></html>');
        self::assertSame('<html></html>', $event->getContent());

        $event->setContent('<html><body></body></html>');
        self::assertSame('<html><body></body></html>', $event->getContent());
    }
}

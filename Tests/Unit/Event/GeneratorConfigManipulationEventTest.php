<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Event;

use SFC\Staticfilecache\Event\GeneratorConfigManipulationEvent;
use SFC\Staticfilecache\Tests\Unit\AbstractTest;

/**
 * GeneratorConfigManipulationEventTest.
 *
 * @internal
 * @coversNothing
 */
final class GeneratorConfigManipulationEventTest extends AbstractTest
{
    public function testGetAndSetConfig(): void
    {
        $event = new GeneratorConfigManipulationEvent(['generator' => 'plain']);
        self::assertSame(['generator' => 'plain'], $event->getConfig());

        $event->setConfig(['generator' => 'gzip']);
        self::assertSame(['generator' => 'gzip'], $event->getConfig());
    }
}

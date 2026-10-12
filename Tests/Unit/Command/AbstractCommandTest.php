<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Command;

use SFC\Staticfilecache\Command\AbstractCommand;
use SFC\Staticfilecache\Tests\Unit\AbstractTest;

/**
 * AbstractCommandTest.
 *
 * @internal
 * @coversNothing
 */
final class AbstractCommandTest extends AbstractTest
{
    public function testDescriptionIsPrefixed(): void
    {
        $command = new class ('staticfilecache:test') extends AbstractCommand {};

        $result = $command->setDescription('Run tests');

        self::assertSame($command, $result);
        self::assertSame('StaticFileCache task: Run tests', $command->getDescription());
    }
}

<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit;

use SFC\Staticfilecache\Exception;

/**
 * ExceptionTest.
 *
 * @internal
 * @coversNothing
 */
final class ExceptionTest extends AbstractTest
{
    public function testExceptionCanBeThrownAndCaught(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Test message');
        $this->expectExceptionCode(123);

        throw new Exception('Test message', 123);
    }

    public function testExceptionIsBaseException(): void
    {
        self::assertInstanceOf(\Exception::class, new Exception());
    }
}

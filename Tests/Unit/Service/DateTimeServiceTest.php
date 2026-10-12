<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Service;

use SFC\Staticfilecache\Service\DateTimeService;
use SFC\Staticfilecache\Tests\Unit\AbstractTest;

/**
 * DateTimeServiceTest.
 *
 * @internal
 * @coversNothing
 */
final class DateTimeServiceTest extends AbstractTest
{
    public function testCurrentTimeIsStableWithinRequest(): void
    {
        $time = (new DateTimeService())->getCurrentTime();

        self::assertGreaterThan(0, $time);
        self::assertLessThanOrEqual(time(), $time);
        self::assertSame($time, (new DateTimeService())->getCurrentTime());
    }
}

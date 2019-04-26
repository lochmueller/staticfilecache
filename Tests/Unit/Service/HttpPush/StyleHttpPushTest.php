<?php

/**
 * StyleHttpPushTest.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Tests\Unit\Service\HttpPush;

use SFC\Staticfilecache\Service\HttpPush\StyleHttpPush;

/**
 * StyleHttpPushTest.
 *
 * @internal
 * @coversNothing
 */
class StyleHttpPushTest extends AbstractHttpPushTest
{
    public function testGetValidHeaders()
    {
        $service = new StyleHttpPush();
        $headers = $service->getHeaders($this->getExampleContent());
        $this->assertCount(1, $headers);
    }
}

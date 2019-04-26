<?php

/**
 * ScriptHttpPush.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Tests\Unit\Service\HttpPush;

use SFC\Staticfilecache\Service\HttpPush\ScriptHttpPush;

/**
 * ScriptHttpPush.
 *
 * @internal
 * @coversNothing
 */
class ScriptHttpPushTest extends AbstractHttpPushTest
{
    public function testGetValidHeaders()
    {
        $service = new ScriptHttpPush();
        $headers = $service->getHeaders($this->getExampleContent());
        $this->assertCount(3, $headers);
    }
}

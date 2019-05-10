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
    /**
     * Test get valid headers.
     */
    public function testGetValidHeaders()
    {
        $service = new ScriptHttpPush();
        $headers = $service->getHeaders($this->getExampleContent());

        $exepected = [
            [
                'path' => '/jquery-3.3.1.slim.min.js',
                'type' => 'script'
            ],
            [
                'path' => '/ajax/libs/popper.js/1.14.7/umd/popper.min.js',
                'type' => 'script'
            ],
            [
                'path' => '/bootstrap/4.3.1/js/bootstrap.min.js',
                'type' => 'script'
            ],
        ];

        $this->assertEquals($exepected, $headers, 'Wrong header result from service');
        $this->assertCount(3, $headers);
    }
}

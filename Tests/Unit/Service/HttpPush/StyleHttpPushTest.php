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
    /**
     * Test get valid headers.
     */
    public function testGetValidHeaders()
    {
        $service = new StyleHttpPush();
        $headers = $service->getHeaders($this->getExampleContent());

        $exepected = [
            [
                'path' => '/bootstrap/4.3.9/css/bootstrap.min.css',
                'type' => 'style',
            ]
        ];

        $this->assertEquals($exepected, $headers, 'Wrong header result from service');
        $this->assertCount(1, $headers);
    }
}

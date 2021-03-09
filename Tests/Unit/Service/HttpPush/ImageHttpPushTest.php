<?php

/**
 * ImageHttpPushTest.
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Service\HttpPush;

use SFC\Staticfilecache\Service\HttpPush\ImageHttpPush;

/**
 * ImageHttpPushTest.
 *
 * @internal
 * @coversNothing
 */
final class ImageHttpPushTest extends AbstractHttpPushTest
{
    /**
     * Test get valid headers.
     */
    public function testGetValidHeaders(): void
    {
        $service = new ImageHttpPush();
        $service->canHandleExtension('jpg');
        $headers = $service->getHeaders($this->getExampleContent());

        $exepected = [
            [
                'path' => '/test1.jpg',
                'type' => 'image',
            ],
        ];

        static::assertSame($exepected, $headers, 'Wrong header result from service');
        static::assertCount(1, $headers);
    }
}

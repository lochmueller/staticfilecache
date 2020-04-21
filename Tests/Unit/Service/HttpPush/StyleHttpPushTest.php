<?php

/**
 * StyleHttpPushTest.
 */

declare(strict_types=1);

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
            ],
            [
                'path' => '/typo3temp/assets/bootstrappackage/fonts/346739da479e213b7b079a21c35f9ffac6feb37c93b4210969602358a8011f68/webfont.css',
                'type' => 'style',
            ],
        ];

        self::assertEquals($exepected, $headers, 'Wrong header result from service');
        self::assertCount(2, $headers);
    }
}

<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Service\HttpPush;

use SFC\Staticfilecache\Service\HttpPush\StyleHttpPush;

/**
 * StyleHttpPushTest.
 *
 * @internal
 * @coversNothing
 */
final class StyleHttpPushTest extends AbstractHttpPushTest
{
    /**
     * Test get valid headers.
     */
    public function testGetValidHeaders(): void
    {
        $service = new StyleHttpPush();
        $headers = $service->getHeaders($this->getExampleContent(), $this->getRequest());

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

        static::assertSame($exepected, $headers, 'Wrong header result from service');
        static::assertCount(2, $headers);
    }

    public function testCanHandleExtension(): void
    {
        $service = new StyleHttpPush();

        static::assertTrue($service->canHandleExtension('css'));
        static::assertFalse($service->canHandleExtension('js'));
    }

    public function testGetNoHeadersWithoutMatches(): void
    {
        static::assertSame([], (new StyleHttpPush())->getHeaders('<html></html>', $this->getRequest()));
    }
}

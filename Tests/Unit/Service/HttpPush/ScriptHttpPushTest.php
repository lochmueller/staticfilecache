<?php

/**
 * ScriptHttpPush.
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Service\HttpPush;

use SFC\Staticfilecache\Service\HttpPush\ScriptHttpPush;

/**
 * ScriptHttpPush.
 *
 * @internal
 * @coversNothing
 */
final class ScriptHttpPushTest extends AbstractHttpPushTest
{
    /**
     * Test get valid headers.
     */
    public function testGetValidHeaders(): void
    {
        $service = new ScriptHttpPush();
        $headers = $service->getHeaders($this->getExampleContent());

        $exepected = [
            [
                'path' => '/typo3conf/ext/bootstrap_package/Resources/Public/Contrib/webfontloader/webfontloader.js',
                'type' => 'script',
            ],
            [
                'path' => '/jquery-3.3.1.slim.min.js',
                'type' => 'script',
            ],
            [
                'path' => '/ajax/libs/popper.js/1.14.7/umd/popper.min.js',
                'type' => 'script',
            ],
            [
                'path' => '/bootstrap/4.3.1/js/bootstrap.min.js',
                'type' => 'script',
            ],
        ];

        static::assertSame($exepected, $headers, 'Wrong header result from service');
        static::assertCount(4, $headers);
    }
}

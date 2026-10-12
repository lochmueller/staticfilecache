<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Service\HttpPush;

use SFC\Staticfilecache\Service\HttpPush\ScriptHttpPush;
use TYPO3\CMS\Core\Http\ServerRequest;

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
        $headers = $service->getHeaders($this->getExampleContent(), $this->getRequest());

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

    public function testCanHandleExtension(): void
    {
        $service = new ScriptHttpPush();

        static::assertTrue($service->canHandleExtension('js'));
        static::assertFalse($service->canHandleExtension('css'));
    }

    public function testGetNoHeadersWithoutMatches(): void
    {
        static::assertSame([], (new ScriptHttpPush())->getHeaders('<html></html>', $this->getRequest()));
    }

    public function testGetHeadersKeepAbsoluteUrlsOnCurrentHost(): void
    {
        $headers = (new ScriptHttpPush())->getHeaders('<script src="https://www.example.com/local.js"></script><script src="https://cdn.example.org/external.js"></script>', $this->getRequest());

        self::assertSame([['path' => '/local.js', 'type' => 'script']], $headers);
    }

    public function testGetNoHeadersWithoutNormalizedParams(): void
    {
        self::assertSame([], (new ScriptHttpPush())->getHeaders('<script src="/local.js"></script>', new ServerRequest()));
    }
}

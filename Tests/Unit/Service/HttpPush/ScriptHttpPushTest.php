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
        $service = $this->getService();
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
        $service = $this->getService();

        static::assertTrue($service->canHandleExtension('js'));
        static::assertFalse($service->canHandleExtension('css'));
    }

    public function testGetNoHeadersWithoutMatches(): void
    {
        static::assertSame([], $this->getService()->getHeaders('<html></html>', $this->getRequest()));
    }

    public function testGetHeadersKeepAbsoluteUrlsOnCurrentHost(): void
    {
        $headers = $this->getService()->getHeaders('<script src="https://www.example.com/local.js"></script><script src="https://cdn.example.org/external.js"></script>', $this->getRequest());

        self::assertSame([['path' => '/local.js', 'type' => 'script']], $headers);
    }

    public function testSkipScriptsWithConfiguredAttributes(): void
    {
        $content = <<<'HTML'
            <script src="/normal.js"></script>
            <script async src="/async.js"></script>
            <script src="/async-value.js" async="async"></script>
            <script defer="" src="/defer.js"></script>
            <script src="/defer-end.js" defer></script>
            <script type="module" src="/module.js"></script>
            <script src="/defer-data.js" data-defer="1"></script>
            <script src="/asynchronous.js" data-type="asynchronous"></script>
            HTML;

        $headers = $this->getService('async,defer')->getHeaders($content, $this->getRequest());

        self::assertSame([
            ['path' => '/normal.js', 'type' => 'script'],
            ['path' => '/module.js', 'type' => 'script'],
            ['path' => '/defer-data.js', 'type' => 'script'],
            ['path' => '/asynchronous.js', 'type' => 'script'],
        ], $headers);
    }

    public function testSkipScriptsWithAttributeValue(): void
    {
        $content = '<script type="module" src="/module.js"></script><script type=module src="/module-unquoted.js"></script><script type="text/javascript" src="/classic.js"></script>';

        $headers = $this->getService('type=module')->getHeaders($content, $this->getRequest());

        self::assertSame([['path' => '/classic.js', 'type' => 'script']], $headers);
    }

    public function testPushAllScriptsWithoutSkipAttributes(): void
    {
        $content = '<script async src="/async.js"></script><script defer src="/defer.js"></script>';

        $headers = $this->getService('')->getHeaders($content, $this->getRequest());

        self::assertSame([
            ['path' => '/async.js', 'type' => 'script'],
            ['path' => '/defer.js', 'type' => 'script'],
        ], $headers);
    }

    public function testGetNoHeadersWithoutNormalizedParams(): void
    {
        self::assertSame([], $this->getService()->getHeaders('<script src="/local.js"></script>', new ServerRequest()));
    }

    protected function getService(string $skipAttributes = ''): ScriptHttpPush
    {
        return new ScriptHttpPush($this->setExtensionConfiguration(['sendHttp2PushSkipScriptAttributes' => $skipAttributes]));
    }
}

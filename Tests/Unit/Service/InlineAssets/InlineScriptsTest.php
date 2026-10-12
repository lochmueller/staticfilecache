<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Service\InlineAssets;

use SFC\Staticfilecache\Service\InlineAssets\InlineScripts;

/**
 * InlineScriptsTest.
 *
 * @internal
 * @coversNothing
 */
final class InlineScriptsTest extends AbstractInlineAssetsTest
{
    public function testCanHandleExtension(): void
    {
        $service = $this->getService(InlineScripts::class);

        self::assertTrue($service->canHandleExtension('js'));
        self::assertFalse($service->canHandleExtension('css'));
    }

    public function testKeepContentWithoutScripts(): void
    {
        $content = '<html><script>inline();</script></html>';

        self::assertSame($content, $this->getService(InlineScripts::class)->replaceInline($content));
    }

    public function testReplaceAndMergeScripts(): void
    {
        $this->createFile('/js/a.js', "first();\n");
        $this->createFile('/js/b.js', 'second();');

        $result = $this->getService(InlineScripts::class)->replaceInline(
            '<body><script src="/js/a.1700000000.js?123"></script> <script async src="/js/b.js"></script></body>'
        );

        self::assertSame('<body><script>first();second();</script></body>', $result);
    }

    public function testMinifyScript(): void
    {
        $this->createFile('/js/app.js', "// comment\nfunction test (a, b) {\n    /* block */\n    return a + b;\n}\n");

        $result = $this->getService(InlineScripts::class, ['inlineScriptMinify' => '1'])->replaceInline('<script src="/js/app.js"></script>');

        self::assertSame("<script>\nfunction test(a,b){return a+b;\n}</script>", $result);
    }
}

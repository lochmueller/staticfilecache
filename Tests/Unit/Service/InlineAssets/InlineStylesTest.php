<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Service\InlineAssets;

use SFC\Staticfilecache\Service\InlineAssets\InlineStyles;

/**
 * InlineStylesTest.
 *
 * @internal
 * @coversNothing
 */
final class InlineStylesTest extends AbstractInlineAssetsTest
{
    public function testCanHandleExtension(): void
    {
        $service = $this->getService(InlineStyles::class);

        self::assertTrue($service->canHandleExtension('css'));
        self::assertFalse($service->canHandleExtension('js'));
    }

    public function testKeepContentWithoutStylesheets(): void
    {
        $content = '<head><link rel="stylesheet" href="/print.css" media="print"></head>';

        self::assertSame($content, $this->getService(InlineStyles::class)->replaceInline($content));
    }

    public function testReplaceAndMergeStylesheets(): void
    {
        $this->createFile('/css/a.css', "@charset \"UTF-8\";body{color:red}\n");
        $this->createFile('/css/b.css', 'p{margin:0}');

        $result = $this->getService(InlineStyles::class)->replaceInline(
            '<head><link rel="stylesheet" href="/css/a.1700000000.css?1"> <link rel="stylesheet" type="text/css" href="/css/b.css"></head>'
        );

        self::assertSame('<head><style>body{color:red}p{margin:0}</style></head>', $result);
    }

    public function testInlineAssetsInStylesheet(): void
    {
        $this->createFile('/css/main.css', 'a{background:url("/img/a.png")}@font-face{src:url("/fonts/f.woff2")}b{background:url("/img/b.jpg")}');
        $this->createFile('/img/a.png', 'png');
        $this->createFile('/fonts/f.woff2', 'font');
        $this->createFile('/img/b.jpg', 'jpg');

        $result = $this->getService(InlineStyles::class, ['inlineStyleAssets' => 'png,woff2'])->replaceInline('<link rel="stylesheet" href="/css/main.css">');

        self::assertSame(
            '<style>a{background:url("data:image/png;base64,' . base64_encode('png') . '")}'
            . '@font-face{src:url("data:font/woff2;base64,' . base64_encode('font') . '")}'
            . 'b{background:url("/img/b.jpg")}</style>',
            $result
        );
    }

    public function testMinifyStylesheet(): void
    {
        $this->createFile('/css/main.css', "/* comment */\nbody {\n    color : red;\n    margin: 0;\n}\n");

        $result = $this->getService(InlineStyles::class, ['inlineStyleMinify' => '1'])->replaceInline('<link rel="stylesheet" href="/css/main.css">');

        self::assertSame('<style> body{color:red;margin:0}</style>', $result);
    }
}

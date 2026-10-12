<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Service\InlineAssets;

use SFC\Staticfilecache\Service\InlineAssets\InlineImages;

/**
 * InlineImagesTest (incl. the asset parsing of AbstractInlineAssets).
 *
 * @internal
 * @coversNothing
 */
final class InlineImagesTest extends AbstractInlineAssetsTest
{
    public function testCanHandleExtension(): void
    {
        $service = $this->getService(InlineImages::class);

        self::assertTrue($service->canHandleExtension('png'));
        self::assertTrue($service->canHandleExtension('jpeg'));
        self::assertFalse($service->canHandleExtension('svg'));
    }

    public function testReplaceImage(): void
    {
        $this->createFile('/img/a.png', 'png-data');

        $result = $this->getService(InlineImages::class)->replaceInline('<img src="/img/a.png" alt="">');

        self::assertSame('<img src="data:image/png;base64,' . base64_encode('png-data') . '" alt="">', $result);
    }

    public function testKeepMissingImage(): void
    {
        $content = '<img src="/img/missing.png">';

        self::assertSame($content, $this->getService(InlineImages::class)->replaceInline($content));
    }

    public function testKeepEmptyImage(): void
    {
        $this->createFile('/img/empty.jpg', '');
        $content = '<img src="/img/empty.jpg">';

        self::assertSame($content, $this->getService(InlineImages::class)->replaceInline($content));
    }

    public function testKeepTooLargeImage(): void
    {
        $this->createFile('/img/large.jpg', str_repeat('x', 100));
        $content = '<img src="/img/large.jpg">';

        self::assertSame($content, $this->getService(InlineImages::class, ['inlineAssetsFileSize' => '10'])->replaceInline($content));
    }

    public function testKeepExternalImage(): void
    {
        $content = '<img src="https://example.com/img/a.png">';

        self::assertSame($content, $this->getService(InlineImages::class)->replaceInline($content));
    }
}

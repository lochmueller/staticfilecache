<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Service\InlineAssets;

use SFC\Staticfilecache\Service\InlineAssets\InlineFavIcon;

/**
 * InlineFavIconTest.
 *
 * @internal
 * @coversNothing
 */
final class InlineFavIconTest extends AbstractInlineAssetsTest
{
    public function testCanHandleExtension(): void
    {
        $service = $this->getService(InlineFavIcon::class);

        self::assertTrue($service->canHandleExtension('ico'));
        self::assertTrue($service->canHandleExtension('svg'));
        self::assertFalse($service->canHandleExtension('jpg'));
    }

    public function testKeepContentWithoutFavIcon(): void
    {
        $content = '<html><head></head></html>';

        self::assertSame($content, $this->getService(InlineFavIcon::class)->replaceInline($content));
    }

    public function testReplaceIcoFavIcon(): void
    {
        $this->createFile('/favicon.ico', 'ico-data');

        $result = $this->getService(InlineFavIcon::class)->replaceInline('<head><link rel="shortcut icon" href="/favicon.ico" type="image/x-icon"></head>');

        self::assertSame('<head><link rel="icon" href="data:image/x-icon;base64,' . base64_encode('ico-data') . '"></head>', $result);
    }

    public function testReplaceSvgFavIcon(): void
    {
        $this->createFile('/favicon.svg', "<svg xmlns=\"http://www.w3.org/2000/svg\">\n    <rect   fill=\"#ff0000\"/>\n</svg>");

        $result = $this->getService(InlineFavIcon::class)->replaceInline('<link rel="icon" href="/favicon.svg">');

        self::assertSame(
            '<link rel="icon" href="data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\'><rect fill=\'%23ff0000\'/></svg>">',
            $result
        );
    }
}

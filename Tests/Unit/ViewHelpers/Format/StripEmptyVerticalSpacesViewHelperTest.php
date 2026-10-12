<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\ViewHelpers\Format;

use SFC\Staticfilecache\Tests\Unit\AbstractTest;
use SFC\Staticfilecache\ViewHelpers\Format\StripEmptyVerticalSpacesViewHelper;

/**
 * StripEmptyVerticalSpacesViewHelperTest.
 *
 * @internal
 * @coversNothing
 */
final class StripEmptyVerticalSpacesViewHelperTest extends AbstractTest
{
    public static function contentDataProvider(): array
    {
        return [
            'empty content' => ['', ''],
            'no empty lines' => ["line1\nline2", "line1\nline2"],
            'empty lines' => ["line1\n\n\nline2\n", "line1\nline2\n"],
            'whitespace only lines' => ["line1\n  \t \n\tline2\n \n", "line1\n\tline2\n"],
            'leading empty lines' => ["\n\n  line1", '  line1'],
            'indentation is kept' => ["<IfModule>\n\n\tExpiresActive on\n</IfModule>", "<IfModule>\n\tExpiresActive on\n</IfModule>"],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('contentDataProvider')]
    public function testRender(string $content, string $expected): void
    {
        $viewHelper = new StripEmptyVerticalSpacesViewHelper();
        $viewHelper->setRenderChildrenClosure(static fn(): string => $content);

        self::assertSame($expected, $viewHelper->render());
    }
}

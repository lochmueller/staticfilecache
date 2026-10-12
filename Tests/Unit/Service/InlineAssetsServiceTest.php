<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Service;

use SFC\Staticfilecache\Service\InlineAssets\InlineScripts;
use SFC\Staticfilecache\Service\InlineAssets\InlineStyles;
use SFC\Staticfilecache\Service\InlineAssetsService;
use SFC\Staticfilecache\Tests\Unit\AbstractTest;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * InlineAssetsServiceTest.
 *
 * @internal
 * @coversNothing
 */
final class InlineAssetsServiceTest extends AbstractTest
{
    public function testKeepContentWithoutEnabledInlineServices(): void
    {
        $this->setExtensionConfiguration(['inlineServiceStyles' => '0', 'boostMode' => '1']);

        self::assertSame('<html></html>', (new InlineAssetsService())->replaceInlineContent('<html></html>'));
    }

    public function testCallEnabledInlineServices(): void
    {
        $this->setExtensionConfiguration(['inlineServiceStyles' => '1', 'inlineServiceScripts' => '0']);

        $inlineStyles = $this->createMock(InlineStyles::class);
        $inlineStyles->expects(self::once())
            ->method('replaceInline')
            ->with('<html></html>')
            ->willReturn('<html>styles</html>');
        GeneralUtility::addInstance(InlineStyles::class, $inlineStyles);

        // Would be used, if the disabled service is called
        GeneralUtility::addInstance(InlineScripts::class, $this->createStub(InlineScripts::class));

        self::assertSame('<html>styles</html>', (new InlineAssetsService())->replaceInlineContent('<html></html>'));

        GeneralUtility::purgeInstances();
    }
}

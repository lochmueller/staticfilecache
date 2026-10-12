<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Generator;

use SFC\Staticfilecache\Generator\HtaccessGenerator;
use SFC\Staticfilecache\Service\DateTimeService;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\TypoScript\AST\Node\RootNode;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;

/**
 * HtaccessGeneratorTest.
 *
 * @internal
 * @coversNothing
 */
final class HtaccessGeneratorTest extends AbstractGeneratorTest
{
    public function testGenerateWriteRenderedTemplateNextToCacheFile(): void
    {
        $this->setExtensionConfiguration();
        $directory = $this->createTemporaryDirectory();

        $generator = new HtaccessGenerator($this->getEventDispatcher(), $this->getViewFactory(" htaccess content \n"));
        $generator->generate($this->getCreateEvent($directory . 'index.html'));

        self::assertStringEqualsFile($directory . '.htaccess', 'htaccess content');
    }

    public function testGenerateAssignVariablesBasedOnLifetime(): void
    {
        $this->setExtensionConfiguration([
            'validHtaccessHeaders' => 'X-Custom',
            'sendCacheControlHeaderRedirectAfterCacheTimeout' => '1',
        ]);
        $directory = $this->createTemporaryDirectory();
        $response = $this->getResponse('', ['X-Custom' => 'value with "quotes"', 'X-Not-Allowed' => 'no']);

        $generator = new HtaccessGenerator($this->getEventDispatcher(), $this->getViewFactory());
        $generator->generate($this->getCreateEvent($directory . 'index.html', $response, 300));

        self::assertSame([
            'contentType' => 'text/html',
            'mode' => 'M',
            'lifetime' => 300,
            'TIME' => '{TIME}',
            'expires' => (new DateTimeService())->getCurrentTime() + 300,
            'sendCacheControlHeader' => false,
            'sendCacheControlHeaderRedirectAfterCacheTimeout' => true,
            'headers' => [
                'X-Custom' => 'value with \"quotes\"',
                'Content-Type' => 'text/html; charset=utf-8',
            ],
        ], $this->assignedVariables);
    }

    public function testGenerateUseAccessTimeout(): void
    {
        $this->setExtensionConfiguration(['htaccessTimeout' => '60']);

        $generator = new HtaccessGenerator($this->getEventDispatcher(), $this->getViewFactory());
        $generator->generate($this->getCreateEvent($this->createTemporaryDirectory() . 'index.html', null, 300));

        self::assertSame('A', $this->assignedVariables['mode']);
        self::assertSame(60, $this->assignedVariables['lifetime']);
    }

    public function testGenerateAddDebugHeader(): void
    {
        $this->setExtensionConfiguration(['debugHeaders' => '1']);

        $generator = new HtaccessGenerator($this->getEventDispatcher(), $this->getViewFactory());
        $generator->generate($this->getCreateEvent($this->createTemporaryDirectory() . 'index.html'));

        self::assertSame('StaticFileCache - via htaccess', $this->assignedVariables['headers']['X-SFC-State']);
    }

    public function testGenerateExtractContentType(): void
    {
        $this->setExtensionConfiguration();
        $response = $this->getResponse('{}', ['Content-Type' => 'application/json; charset=utf-8']);

        $generator = new HtaccessGenerator($this->getEventDispatcher(), $this->getViewFactory());
        $generator->generate($this->getCreateEvent($this->createTemporaryDirectory() . 'index.html', $response));

        self::assertSame('application/json', $this->assignedVariables['contentType']);
    }

    /**
     * @return array<string, array{array<string, string>, bool}>
     */
    public static function sendCacheHeadersDataProvider(): array
    {
        return [
            'enabled' => [['sendCacheHeaders' => '1'], true],
            'disabled' => [['sendCacheHeaders' => '0'], false],
            'not set' => [[], false],
        ];
    }

    /**
     * @param array<string, string> $configArray
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('sendCacheHeadersDataProvider')]
    public function testGenerateReadSendCacheHeadersFromFrontendTypoScript(array $configArray, bool $expected): void
    {
        $this->setExtensionConfiguration();
        $frontendTypoScript = new FrontendTypoScript(new RootNode(), [], [], []);
        $frontendTypoScript->setConfigArray($configArray);
        $GLOBALS['TYPO3_REQUEST'] = (new ServerRequest('https://example.com/'))->withAttribute('frontend.typoscript', $frontendTypoScript);

        $generator = new HtaccessGenerator($this->getEventDispatcher(), $this->getViewFactory());
        $generator->generate($this->getCreateEvent($this->createTemporaryDirectory() . 'index.html'));

        self::assertSame($expected, $this->assignedVariables['sendCacheControlHeader']);
    }

    public function testCleanupHeaderValuesRespectMaxHeaderSize(): void
    {
        $this->setExtensionConfiguration(['maxHeaderSize' => '10', 'headerSizeBuffer' => '2']);
        $generator = new HtaccessGenerator($this->getEventDispatcher(), $this->getViewFactory());

        $result = (new \ReflectionMethod($generator, 'cleanupHeaderValues'))->invoke($generator, [
            'X-Long' => '1234567890',
            'X-Quote' => '"a"',
        ]);

        self::assertSame(['X-Long' => '12345', 'X-Quote' => '\"a\"'], $result);
    }

    public function testDefaultTemplateName(): void
    {
        $this->setExtensionConfiguration();
        $generator = new HtaccessGenerator($this->getEventDispatcher(), $this->getViewFactory());

        self::assertSame(
            'EXT:staticfilecache/Resources/Private/Templates/Htaccess.html',
            (new \ReflectionMethod($generator, 'getTemplateName'))->invoke($generator)
        );
    }

    public function testCustomTemplateName(): void
    {
        $this->setExtensionConfiguration(['htaccessTemplateName' => ' fileadmin/Htaccess.html ']);

        $generator = new HtaccessGenerator($this->getEventDispatcher(), $this->getViewFactory());
        $generator->generate($this->getCreateEvent($this->createTemporaryDirectory() . 'index.html'));

        self::assertStringEndsWith('/fileadmin/Htaccess.html', (string) $this->renderedTemplate);
    }

    public function testRemoveHtaccessFile(): void
    {
        $directory = $this->createTemporaryDirectory();
        file_put_contents($directory . '.htaccess', 'content');

        $generator = new HtaccessGenerator($this->getEventDispatcher(), $this->getViewFactory());
        $generator->remove($this->getRemoveEvent($directory . 'index.html'));

        self::assertFileDoesNotExist($directory . '.htaccess');
    }
}

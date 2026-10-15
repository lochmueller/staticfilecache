<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Generator;

use Psr\EventDispatcher\EventDispatcherInterface;
use SFC\Staticfilecache\Event\GeneratorConfigManipulationEvent;
use SFC\Staticfilecache\Generator\ConfigGenerator;
use SFC\Staticfilecache\Service\DateTimeService;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\TypoScript\AST\Node\RootNode;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;

/**
 * ConfigGeneratorTest.
 *
 * @internal
 * @coversNothing
 */
final class ConfigGeneratorTest extends AbstractGeneratorTest
{
    public function testGenerateWriteConfigJson(): void
    {
        $this->setExtensionConfiguration(['validFallbackHeaders' => 'X-Custom']);
        $fileName = $this->createTemporaryDirectory() . 'index.html';
        $response = $this->getResponse('<html></html>', ['X-Custom' => 'yes', 'X-Not-Allowed' => 'no']);

        $generator = new ConfigGenerator($this->getEventDispatcher(), $this->getViewFactory());
        $generator->generate($this->getCreateEvent($fileName, $response, 120));

        self::assertFileExists($fileName . '.config.json');
        $config = json_decode((string) file_get_contents($fileName . '.config.json'), true);

        $time = (new DateTimeService())->getCurrentTime();
        self::assertSame($time, $config['generatedTimestamp']);
        self::assertSame($time + 120, $config['invalidAtTimestamp']);
        self::assertSame(date('r', $time), $config['generated']);
        self::assertSame(date('r', $time + 120), $config['invalidAt']);
        self::assertSame([
            'X-Custom' => 'yes',
            'Content-Type' => 'text/html; charset=utf-8',
        ], $config['headers']);
    }

    public function testGenerateStoreCacheHeaderConfigurationOfThePage(): void
    {
        $this->setExtensionConfiguration();
        $frontendTypoScript = new FrontendTypoScript(new RootNode(), [], [], []);
        $frontendTypoScript->setConfigArray(['sendCacheHeaders' => '1', 'sendCacheHeadersForSharedCaches' => 'auto']);
        $GLOBALS['TYPO3_REQUEST'] = (new ServerRequest('https://example.com/'))->withAttribute('frontend.typoscript', $frontendTypoScript);
        $fileName = $this->createTemporaryDirectory() . 'index.html';

        $generator = new ConfigGenerator($this->getEventDispatcher(), $this->getViewFactory());
        $generator->generate($this->getCreateEvent($fileName));

        $config = json_decode((string) file_get_contents($fileName . '.config.json'), true);
        self::assertTrue($config['sendCacheHeaders']);
        self::assertSame('auto', $config['sendCacheHeadersForSharedCaches']);
    }

    public function testGenerateStoreNoCacheHeaderConfigurationWithoutRequest(): void
    {
        $this->setExtensionConfiguration();
        unset($GLOBALS['TYPO3_REQUEST']);
        $fileName = $this->createTemporaryDirectory() . 'index.html';

        $generator = new ConfigGenerator($this->getEventDispatcher(), $this->getViewFactory());
        $generator->generate($this->getCreateEvent($fileName));

        $config = json_decode((string) file_get_contents($fileName . '.config.json'), true);
        self::assertFalse($config['sendCacheHeaders']);
        self::assertSame('', $config['sendCacheHeadersForSharedCaches']);
    }

    public function testGenerateUseManipulatedConfig(): void
    {
        $this->setExtensionConfiguration();
        $fileName = $this->createTemporaryDirectory() . 'index.html';

        $eventDispatcher = $this->createStub(EventDispatcherInterface::class);
        $eventDispatcher->method('dispatch')->willReturnCallback(static function (GeneratorConfigManipulationEvent $event): object {
            $event->setConfig(['manipulated' => true]);

            return $event;
        });

        $generator = new ConfigGenerator($eventDispatcher, $this->getViewFactory());
        $generator->generate($this->getCreateEvent($fileName));

        self::assertSame(['manipulated' => true], json_decode((string) file_get_contents($fileName . '.config.json'), true));
    }

    public function testRemoveFile(): void
    {
        $fileName = $this->createTemporaryDirectory() . 'index.html';
        file_put_contents($fileName . '.config.json', '{}');

        $generator = new ConfigGenerator($this->getEventDispatcher(), $this->getViewFactory());
        $generator->remove($this->getRemoveEvent($fileName));

        self::assertFileDoesNotExist($fileName . '.config.json');
    }
}

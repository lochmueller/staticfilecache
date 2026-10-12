<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Generator;

use Psr\EventDispatcher\EventDispatcherInterface;
use SFC\Staticfilecache\Event\GeneratorConfigManipulationEvent;
use SFC\Staticfilecache\Generator\ConfigGenerator;
use SFC\Staticfilecache\Service\DateTimeService;

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

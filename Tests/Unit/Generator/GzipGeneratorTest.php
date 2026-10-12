<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Generator;

use SFC\Staticfilecache\Generator\GzipGenerator;

/**
 * GzipGeneratorTest.
 *
 * @internal
 * @coversNothing
 */
final class GzipGeneratorTest extends AbstractGeneratorTest
{
    public function testGenerateDoNothingIfDisabled(): void
    {
        $this->setExtensionConfiguration(['enableGeneratorGzip' => '0']);
        $fileName = $this->createTemporaryDirectory() . 'index.html';

        $generator = new GzipGenerator($this->getEventDispatcher(), $this->getViewFactory());
        $generator->generate($this->getCreateEvent($fileName));

        self::assertFileDoesNotExist($fileName . '.gz');
    }

    public function testGenerateWriteCompressedManipulatedContent(): void
    {
        $this->setExtensionConfiguration(['enableGeneratorGzip' => '1']);
        $fileName = $this->createTemporaryDirectory() . 'index.html';

        $generator = new GzipGenerator($this->getEventDispatcher(), $this->getViewFactory());
        $generator->generate($this->getCreateEvent($fileName, $this->getResponse('<html>Gzip</html>')));

        self::assertFileExists($fileName . '.gz');
        self::assertSame('manipulated:<html>Gzip</html>', gzdecode((string) file_get_contents($fileName . '.gz')));
    }

    public static function compressionLevelDataProvider(): array
    {
        return [
            'not configured' => [null, GzipGenerator::DEFAULT_COMPRESSION_LEVEL],
            'valid level' => [7, 7],
            'zero falls back to default' => [0, GzipGenerator::DEFAULT_COMPRESSION_LEVEL],
            'too low' => [-5, 1],
            'too high' => [42, 9],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('compressionLevelDataProvider')]
    public function testCompressionLevel(?int $configuredLevel, int $expectedLevel): void
    {
        unset($GLOBALS['TYPO3_CONF_VARS']['FE']['compressionLevel']);
        if (null !== $configuredLevel) {
            $GLOBALS['TYPO3_CONF_VARS']['FE']['compressionLevel'] = $configuredLevel;
        }

        $generator = new GzipGenerator($this->getEventDispatcher(), $this->getViewFactory());
        $level = (new \ReflectionMethod($generator, 'getCompressionLevel'))->invoke($generator);

        self::assertSame($expectedLevel, $level);
    }

    public function testRemoveFile(): void
    {
        $fileName = $this->createTemporaryDirectory() . 'index.html';
        file_put_contents($fileName . '.gz', 'content');
        file_put_contents($fileName, 'content');

        $generator = new GzipGenerator($this->getEventDispatcher(), $this->getViewFactory());
        $generator->remove($this->getRemoveEvent($fileName));

        self::assertFileDoesNotExist($fileName . '.gz');
        self::assertFileExists($fileName, 'Only the gz file should be removed');
    }
}

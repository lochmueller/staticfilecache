<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Generator;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use SFC\Staticfilecache\Generator\BrotliGenerator;

/**
 * BrotliGeneratorTest.
 *
 * @internal
 * @coversNothing
 */
final class BrotliGeneratorTest extends AbstractGeneratorTest
{
    public function testGenerateDoNothingIfDisabled(): void
    {
        $this->setExtensionConfiguration(['enableGeneratorBrotli' => '0']);
        $fileName = $this->createTemporaryDirectory() . 'index.html';

        $generator = new BrotliGenerator($this->getEventDispatcher(), $this->getViewFactory());
        $generator->setLogger(new NullLogger());
        $generator->generate($this->getCreateEvent($fileName));

        self::assertFileDoesNotExist($fileName . '.br');
    }

    public function testGenerateLogErrorIfBrotliIsNotAvailable(): void
    {
        if (\function_exists('brotli_compress')) {
            self::markTestSkipped('Brotli extension is installed');
        }
        $this->setExtensionConfiguration(['enableGeneratorBrotli' => '1']);
        $fileName = $this->createTemporaryDirectory() . 'index.html';

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error');

        $generator = new BrotliGenerator($this->getEventDispatcher(), $this->getViewFactory());
        $generator->setLogger($logger);
        $generator->generate($this->getCreateEvent($fileName));

        self::assertFileDoesNotExist($fileName . '.br');
    }

    public function testGenerateWriteCompressedManipulatedContent(): void
    {
        if (!\function_exists('brotli_compress')) {
            self::markTestSkipped('Brotli extension is not installed');
        }
        $this->setExtensionConfiguration(['enableGeneratorBrotli' => '1']);
        $fileName = $this->createTemporaryDirectory() . 'index.html';

        $generator = new BrotliGenerator($this->getEventDispatcher(), $this->getViewFactory());
        $generator->setLogger(new NullLogger());
        $generator->generate($this->getCreateEvent($fileName, $this->getResponse('<html>Brotli</html>')));

        self::assertSame('manipulated:<html>Brotli</html>', brotli_uncompress((string) file_get_contents($fileName . '.br')));
    }

    public function testRemoveFile(): void
    {
        $fileName = $this->createTemporaryDirectory() . 'index.html';
        file_put_contents($fileName . '.br', 'content');

        $generator = new BrotliGenerator($this->getEventDispatcher(), $this->getViewFactory());
        $generator->remove($this->getRemoveEvent($fileName));

        self::assertFileDoesNotExist($fileName . '.br');
    }
}

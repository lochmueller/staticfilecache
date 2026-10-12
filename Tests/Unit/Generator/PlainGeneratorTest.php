<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Generator;

use SFC\Staticfilecache\Generator\PlainGenerator;

/**
 * PlainGeneratorTest.
 *
 * @internal
 * @coversNothing
 */
final class PlainGeneratorTest extends AbstractGeneratorTest
{
    public function testGenerateDoNothingIfDisabled(): void
    {
        $this->setExtensionConfiguration(['enableGeneratorPlain' => '0']);
        $fileName = $this->createTemporaryDirectory() . 'index.html';

        $generator = new PlainGenerator($this->getEventDispatcher(), $this->getViewFactory());
        $generator->generate($this->getCreateEvent($fileName));

        self::assertFileDoesNotExist($fileName);
    }

    public function testGenerateWriteManipulatedContent(): void
    {
        $this->setExtensionConfiguration(['enableGeneratorPlain' => '1']);
        $fileName = $this->createTemporaryDirectory() . 'index.html';

        $generator = new PlainGenerator($this->getEventDispatcher(), $this->getViewFactory());
        $generator->generate($this->getCreateEvent($fileName, $this->getResponse('<html>Plain</html>')));

        self::assertStringEqualsFile($fileName, 'manipulated:<html>Plain</html>');
    }

    public function testRemoveFile(): void
    {
        $fileName = $this->createTemporaryDirectory() . 'index.html';
        file_put_contents($fileName, 'content');

        $generator = new PlainGenerator($this->getEventDispatcher(), $this->getViewFactory());
        $generator->remove($this->getRemoveEvent($fileName));

        self::assertFileDoesNotExist($fileName);
    }
}

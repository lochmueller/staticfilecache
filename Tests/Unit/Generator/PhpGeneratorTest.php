<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Generator;

use SFC\Staticfilecache\Generator\PhpGenerator;
use SFC\Staticfilecache\Service\DateTimeService;

/**
 * PhpGeneratorTest.
 *
 * @internal
 * @coversNothing
 */
final class PhpGeneratorTest extends AbstractGeneratorTest
{
    public function testGenerateDoNothingIfDisabled(): void
    {
        $this->setExtensionConfiguration(['enableGeneratorPhp' => '0']);
        $fileName = $this->createTemporaryDirectory() . 'index.html';

        $generator = new PhpGenerator($this->getEventDispatcher(), $this->getViewFactory());
        $generator->generate($this->getCreateEvent($fileName));

        self::assertNull($this->assignedVariables);
        self::assertFileDoesNotExist($fileName . '.php');
    }

    public function testGenerateWriteRenderedTemplate(): void
    {
        $this->setExtensionConfiguration([
            'enableGeneratorPhp' => '1',
            'validHtaccessHeaders' => 'X-Custom',
            'debugHeaders' => '1',
        ]);
        $_SERVER['REQUEST_URI'] = '/page/';
        $fileName = $this->createTemporaryDirectory() . 'index.html';
        $response = $this->getResponse('<html>Php</html>', ['X-Custom' => "it's"]);

        $generator = new PhpGenerator($this->getEventDispatcher(), $this->getViewFactory('<?php echo "x";'));
        $generator->generate($this->getCreateEvent($fileName, $response, 500));

        self::assertStringEqualsFile($fileName . '.php', '<?php echo "x";');
        self::assertSame([
            'expires' => (new DateTimeService())->getCurrentTime() + 500,
            'sendCacheControlHeaderRedirectAfterCacheTimeout' => false,
            'headers' => [
                'X-Custom' => "it\\'s",
                'Content-Type' => 'text/html; charset=utf-8',
                'X-SFC-State' => 'StaticFileCache - via PhpGenerator',
            ],
            'requestUri' => '/page/',
            'body' => 'manipulated:<html>Php</html>',
        ], $this->assignedVariables);
    }

    public function testGenerateUseAccessTimeout(): void
    {
        $this->setExtensionConfiguration(['enableGeneratorPhp' => '1', 'htaccessTimeout' => '60']);

        $generator = new PhpGenerator($this->getEventDispatcher(), $this->getViewFactory());
        $generator->generate($this->getCreateEvent($this->createTemporaryDirectory() . 'index.html', null, 500));

        self::assertSame((new DateTimeService())->getCurrentTime() + 60, $this->assignedVariables['expires']);
    }

    public function testDefaultTemplateName(): void
    {
        $this->setExtensionConfiguration();
        $generator = new PhpGenerator($this->getEventDispatcher(), $this->getViewFactory());

        self::assertSame(
            'EXT:staticfilecache/Resources/Private/Templates/Php.html',
            (new \ReflectionMethod($generator, 'getTemplateName'))->invoke($generator)
        );
    }

    public function testCustomTemplateName(): void
    {
        $this->setExtensionConfiguration(['enableGeneratorPhp' => '1', 'phpTemplateName' => 'fileadmin/Php.html']);

        $generator = new PhpGenerator($this->getEventDispatcher(), $this->getViewFactory());
        $generator->generate($this->getCreateEvent($this->createTemporaryDirectory() . 'index.html'));

        self::assertStringEndsWith('/fileadmin/Php.html', (string) $this->renderedTemplate);
    }

    public function testRemoveFile(): void
    {
        $fileName = $this->createTemporaryDirectory() . 'index.html';
        file_put_contents($fileName . '.php', 'content');

        $generator = new PhpGenerator($this->getEventDispatcher(), $this->getViewFactory());
        $generator->remove($this->getRemoveEvent($fileName));

        self::assertFileDoesNotExist($fileName . '.php');
    }
}

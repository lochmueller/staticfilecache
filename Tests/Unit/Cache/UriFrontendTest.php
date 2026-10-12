<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Cache;

use SFC\Staticfilecache\Cache\IdentifierBuilder;
use SFC\Staticfilecache\Cache\UriFrontend;
use SFC\Staticfilecache\Tests\Unit\AbstractTest;
use TYPO3\CMS\Core\Cache\Backend\BackendInterface;
use TYPO3\CMS\Core\Cache\Backend\TaggableBackendInterface;
use TYPO3\CMS\Core\Cache\Backend\TransientMemoryBackend;
use TYPO3\CMS\Core\EventDispatcher\NoopEventDispatcher;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * UriFrontendTest.
 *
 * @internal
 * @coversNothing
 */
final class UriFrontendTest extends AbstractTest
{
    public function testValidEntryIdentifier(): void
    {
        GeneralUtility::addInstance(IdentifierBuilder::class, new IdentifierBuilder(new NoopEventDispatcher()));
        GeneralUtility::addInstance(IdentifierBuilder::class, new IdentifierBuilder(new NoopEventDispatcher()));
        $frontend = new UriFrontend('staticfilecache', new TransientMemoryBackend());

        self::assertTrue($frontend->isValidEntryIdentifier('https://www.example.com/page/'));
        self::assertFalse($frontend->isValidEntryIdentifier('/page/'));
    }

    public function testEntryIdentifierIsInvalidIfIdentifierBuilderCouldNotBeCreated(): void
    {
        // Without DI container the IdentifierBuilder could not fetch the event dispatcher
        $frontend = new UriFrontend('staticfilecache', new TransientMemoryBackend());

        self::assertFalse($frontend->isValidEntryIdentifier('https://www.example.com/page/'));
    }

    public function testGetByTagWithTransientBackend(): void
    {
        $backend = new TransientMemoryBackend();
        $frontend = new UriFrontend('staticfilecache', $backend);
        $backend->set('https://example.com/a', ['priority' => 1], ['pageId_1']);
        $backend->set('https://example.com/b', ['priority' => 2], ['pageId_2']);

        self::assertSame(['https://example.com/a' => ['priority' => 1]], $frontend->getByTag('pageId_1'));
        self::assertSame([], $frontend->getByTag('pageId_3'));
    }

    public function testGetByTagUnserializeDataOfNonTransientBackend(): void
    {
        $backend = $this->createMock(TaggableBackendInterface::class);
        $backend->expects(self::once())->method('findIdentifiersByTag')->with('pageId_1')->willReturn(['https://example.com/a', 'https://example.com/b']);
        $backend->method('get')->willReturnMap([
            ['https://example.com/a', serialize(['priority' => 1])],
            ['https://example.com/b', false],
        ]);
        $frontend = new UriFrontend('staticfilecache', $backend);

        self::assertSame(['https://example.com/a' => ['priority' => 1]], $frontend->getByTag('pageId_1'));
    }

    public function testGetByTagThrowExceptionOnInvalidTag(): void
    {
        $frontend = new UriFrontend('staticfilecache', new TransientMemoryBackend());

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1233058312);

        $frontend->getByTag('invalid tag!');
    }

    public function testGetByTagThrowExceptionOnNonTaggableBackend(): void
    {
        $frontend = new UriFrontend('staticfilecache', $this->createStub(BackendInterface::class));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(12389123);

        $frontend->getByTag('pageId_1');
    }
}

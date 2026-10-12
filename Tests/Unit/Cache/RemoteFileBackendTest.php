<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Cache;

use SFC\Staticfilecache\Cache\RemoteFileBackend;
use SFC\Staticfilecache\Exception;
use SFC\Staticfilecache\Tests\Unit\AbstractTest;
use TYPO3\CMS\Core\Cache\Exception\InvalidDataException;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * RemoteFileBackendTest.
 *
 * @internal
 * @coversNothing
 */
final class RemoteFileBackendTest extends AbstractTest
{
    private string $storageFolder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->storageFolder = GeneralUtility::getFileAbsFileName(RemoteFileBackend::RELATIVE_STORAGE_FOLDER);
        GeneralUtility::rmdir($this->storageFolder, true);

        $storage = $this->createStub(ResourceStorage::class);
        $storage->method('sanitizeFileName')->willReturnCallback(static fn(string $fileName): string => str_replace(' ', '_', $fileName));
        $storageRepository = $this->createStub(StorageRepository::class);
        $storageRepository->method('getDefaultStorage')->willReturn($storage);
        $this->registerContainerServices([StorageRepository::class => $storageRepository]);
    }

    protected function tearDown(): void
    {
        GeneralUtility::rmdir($this->storageFolder, true);
        parent::tearDown();
    }

    public function testSetAndGet(): void
    {
        $backend = $this->getBackend();
        $identifier = 'https://example.com/assets/my%20image.png';

        self::assertFalse($backend->has($identifier));
        self::assertFalse($backend->get($identifier));

        $backend->set($identifier, 'binary-content', ['tag1'], 3600);

        self::assertTrue($backend->has($identifier));
        $relativePath = $backend->get($identifier);
        self::assertMatchesRegularExpression('#^typo3temp/remote/([0-9a-f]/){3}my_image\.png$#', $relativePath);
        $absolutePath = GeneralUtility::getFileAbsFileName($relativePath);
        self::assertSame('binary-content', file_get_contents($absolutePath));
        self::assertSame('|tag1|', file_get_contents($absolutePath . RemoteFileBackend::FILE_EXTENSION_TAG));
        self::assertSame($identifier, file_get_contents($absolutePath . RemoteFileBackend::FILE_EXTENSION_IDENTIFIER));
    }

    public function testHashLengthDefinesFolderStructure(): void
    {
        $backend = $this->getBackend();
        $backend->setHashLength(4);
        $backend->set('https://example.com/file.txt', 'content');

        self::assertMatchesRegularExpression('#^typo3temp/remote/([0-9a-f]/){4}file\.txt$#', $backend->get('https://example.com/file.txt'));
    }

    public function testSetWithUnlimitedLifetime(): void
    {
        $backend = $this->getBackend();
        $backend->set('https://example.com/file.txt', 'content', [], 0);

        $lifetimeFile = GeneralUtility::getFileAbsFileName($backend->get('https://example.com/file.txt')) . RemoteFileBackend::FILE_EXTENSION_LIFETIME;
        $expected = (new \DateTime(RemoteFileBackend::DATETIME_EXPIRYTIME_UNLIMITED_INTERNAL))->getTimestamp();
        self::assertSame((string) $expected, file_get_contents($lifetimeFile));
    }

    public function testSetFetchContentIfDataIsEmpty(): void
    {
        $sourceFile = $this->createTemporaryDirectory() . 'source.txt';
        file_put_contents($sourceFile, 'fetched content');
        $backend = $this->getBackend();

        $backend->set($sourceFile, '');

        self::assertSame('fetched content', file_get_contents(GeneralUtility::getFileAbsFileName($backend->get($sourceFile))));
    }

    public function testSetThrowExceptionIfContentCouldNotBeFetched(): void
    {
        $this->expectException(InvalidDataException::class);
        $this->expectExceptionCode(56757677);

        $this->getBackend()->set($this->createTemporaryDirectory() . 'missing.txt', null);
    }

    public function testInvalidIdentifierThrowException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionCode(23478);

        $this->getBackend()->has('https://example.com');
    }

    public function testRemove(): void
    {
        $backend = $this->getBackend();
        self::assertFalse($backend->remove('https://example.com/file.txt'));

        $backend->set('https://example.com/file.txt', 'content');
        $absolutePath = GeneralUtility::getFileAbsFileName($backend->get('https://example.com/file.txt'));

        self::assertTrue($backend->remove('https://example.com/file.txt'));
        self::assertFalse($backend->has('https://example.com/file.txt'));
        self::assertFileDoesNotExist($absolutePath . RemoteFileBackend::FILE_EXTENSION_TAG);
        self::assertFileDoesNotExist($absolutePath . RemoteFileBackend::FILE_EXTENSION_LIFETIME);
        self::assertFileDoesNotExist($absolutePath . RemoteFileBackend::FILE_EXTENSION_IDENTIFIER);
    }

    public function testFindIdentifiersAndFlushByTag(): void
    {
        $backend = $this->getBackend();
        $backend->set('https://example.com/a.txt', 'a', ['tag1', 'tag2']);
        $backend->set('https://example.com/b.txt', 'b', ['tag2']);
        $backend->set('https://example.com/c.txt', 'c', ['tag3']);

        $identifiers = $backend->findIdentifiersByTag('tag2');
        sort($identifiers);
        self::assertSame(['https://example.com/a.txt', 'https://example.com/b.txt'], $identifiers);
        self::assertSame([], $backend->findIdentifiersByTag('tag'));

        $backend->flushByTag('tag1');
        self::assertFalse($backend->has('https://example.com/a.txt'));
        self::assertTrue($backend->has('https://example.com/b.txt'));

        $backend->flushByTags(['tag2', 'tag3']);
        self::assertFalse($backend->has('https://example.com/b.txt'));
        self::assertFalse($backend->has('https://example.com/c.txt'));
    }

    public function testCollectGarbageRemoveExpiredEntries(): void
    {
        $backend = $this->getBackend();
        $backend->set('https://example.com/expired.txt', 'content');
        $backend->set('https://example.com/valid.txt', 'content');
        $lifetimeFile = GeneralUtility::getFileAbsFileName($backend->get('https://example.com/expired.txt')) . RemoteFileBackend::FILE_EXTENSION_LIFETIME;
        file_put_contents($lifetimeFile, (string) (time() - 10));

        self::assertFalse($backend->has('https://example.com/expired.txt'));

        $backend->collectGarbage();

        self::assertFileDoesNotExist($lifetimeFile);
        self::assertTrue($backend->has('https://example.com/valid.txt'));
    }

    public function testFlush(): void
    {
        $backend = $this->getBackend();
        $backend->set('https://example.com/file.txt', 'content');

        $backend->flush();

        self::assertDirectoryDoesNotExist($this->storageFolder);
        self::assertFalse($backend->has('https://example.com/file.txt'));
    }

    private function getBackend(): RemoteFileBackend
    {
        return new RemoteFileBackend(['defaultLifetime' => 3600]);
    }
}

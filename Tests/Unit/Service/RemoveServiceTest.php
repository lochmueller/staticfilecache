<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Service;

use SFC\Staticfilecache\Service\RemoveService;
use SFC\Staticfilecache\Tests\Unit\AbstractTest;

/**
 * RemoveServiceTest.
 *
 * @internal
 * @coversNothing
 */
final class RemoveServiceTest extends AbstractTest
{
    public function testRemoveExistingFile(): void
    {
        $fileName = $this->createTemporaryDirectory() . 'file.html';
        file_put_contents($fileName, 'content');

        self::assertTrue((new RemoveService())->file($fileName));
        self::assertFileDoesNotExist($fileName);
    }

    public function testRemoveMissingFileIsSuccessful(): void
    {
        self::assertTrue((new RemoveService())->file($this->createTemporaryDirectory() . 'missing.html'));
    }

    public function testDirectoryIsRenamedAndRemovedLater(): void
    {
        $directory = $this->createTemporaryDirectory();
        mkdir($directory . 'sub');
        file_put_contents($directory . 'sub/index.html', 'content');

        $removeService = new RemoveService();
        self::assertSame($removeService, $removeService->directory($directory . 'sub/'));

        self::assertDirectoryDoesNotExist($directory . 'sub', 'Directory should be renamed directly');
        $renamedDirectories = glob($directory . 'sub_*', GLOB_ONLYDIR);
        self::assertCount(1, $renamedDirectories);

        $removeService->removeQueueDirectories();
        self::assertDirectoryDoesNotExist($renamedDirectories[0]);
    }

    public function testAlreadyRenamedDirectoryIsNotRenamedAgain(): void
    {
        $directory = $this->createTemporaryDirectory() . 'sub_1700000000000/';
        mkdir($directory);

        $removeService = new RemoveService();
        $removeService->directory($directory);
        self::assertDirectoryExists($directory);

        $removeService->removeQueueDirectories();
        self::assertDirectoryDoesNotExist($directory);
    }

    public function testMissingDirectoryIsIgnored(): void
    {
        $removeService = new RemoveService();

        self::assertSame($removeService, $removeService->directory($this->createTemporaryDirectory() . 'missing/'));
        self::assertSame($removeService, $removeService->subdirectories($this->createTemporaryDirectory() . 'missing/'));
    }

    public function testSubdirectoriesAreRemovedButFilesAreKept(): void
    {
        $directory = $this->createTemporaryDirectory();
        mkdir($directory . 'first');
        mkdir($directory . 'second');
        file_put_contents($directory . 'file.html', 'content');

        $removeService = new RemoveService();
        $removeService->subdirectories($directory);
        $removeService->removeQueueDirectories();

        self::assertSame(['file.html'], array_values(array_diff(scandir($directory), ['.', '..'])));
    }

    public function testDirectoriesAreRemovedOnDestruct(): void
    {
        $directory = $this->createTemporaryDirectory();
        mkdir($directory . 'sub');

        $removeService = new RemoveService();
        $removeService->directory($directory . 'sub/');
        unset($removeService);

        self::assertSame([], array_values(array_diff(scandir($directory), ['.', '..'])));
    }
}

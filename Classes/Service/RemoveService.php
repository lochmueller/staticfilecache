<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Service;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class RemoveService
{
    /**
     * Dirs that are created with "softRemoveDir" and dropped with "runRemoveDir".
     */
    protected array $removeDirs = [];

    /**
     * Finally remove the dirs.
     */
    public function __destruct()
    {
        $this->removeQueueDirectories();
    }

    public function removeQueueDirectories(): void
    {
        foreach ($this->removeDirs as $removeDir) {
            GeneralUtility::rmdir($removeDir, true);
        }
        $this->removeDirs = [];
    }

    /**
     * Remove the given file. If the file do not exists, the function return true.
     */
    public function file(string $absoulteFileName): bool
    {
        if (!is_file($absoulteFileName)) {
            return true;
        }


        if (!@unlink($absoulteFileName)) {
            if (!@is_writable($absoulteFileName)) {
                throw new \RuntimeException('Could not remove file: ' . $absoulteFileName, 123678123);
            }
            // Return true if the file no longer exists (don't care _what_ removed
            // the file, as long as it's gone).
            // But return false if the file still exists but couldn't be removed
            // by unlink() (for reasons other than write permissions).
            // Alternatively, throw an exception if file_exists($file) informing that
            // the file couldn't be removed and this is an error causing stale caches.
            return !file_exists($absoulteFileName);
        }
        return true;
    }

    /**
     * Add the subdirectories of the given folder to the remove function.
     */
    public function subdirectories(string $absoluteDirName): self
    {
        if (!is_dir($absoluteDirName)) {
            return $this;
        }

        foreach (new \DirectoryIterator($absoluteDirName) as $item) {
            /** @var \DirectoryIterator $item */
            if ($item->isDir() && !$item->isDot()) {
                $this->directory($item->getPathname() . '/');
            }
        }

        return $this;
    }

    /**
     * Rename the dir and mark them as "to remove".
     * Speed up the remove process.
     */
    public function directory(string $absoluteDirName): self
    {
        if (is_dir($absoluteDirName)) {
            $alreadyRenamed = (bool) \preg_match('/.*([0-9]{11,14})$/', rtrim($absoluteDirName, '/'));
            if ($alreadyRenamed) {
                $this->removeDirs[] = $absoluteDirName;
            } else {
                $tempAbsoluteDir = rtrim($absoluteDirName, '/') . '_' . round(microtime(true) * 1000) . '/';
                rename($absoluteDirName, $tempAbsoluteDir);
                $this->removeDirs[] = $tempAbsoluteDir;
            }
        }

        return $this;
    }
}

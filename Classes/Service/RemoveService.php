<?php
/**
 * RemoveService.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Service;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * RemoveService.
 */
class RemoveService extends AbstractService
{
    /**
     * Dirs that are created with "softRemoveDir" and dropped with "runRemoveDir".
     *
     * @var array
     */
    protected $removeDirs = [];

    /**
     * Remove the given file. If the file do not exists, the function return true.
     *
     * @param string $absoulteFileName
     *
     * @return bool
     */
    public function file(string $absoulteFileName): bool
    {
        if (!\is_file($absoulteFileName)) {
            return true;
        }

        return (bool)\unlink($absoulteFileName);
    }

    /**
     * Add the subdirecotries of thee given folder to the remove function.
     *
     * @param string $absoluteDirName
     *
     * @return RemoveService
     */
    public function subdirectories(string $absoluteDirName): self
    {
        if (!\is_dir($absoluteDirName)) {
            return $this;
        }

        foreach (new \DirectoryIterator($absoluteDirName) as $item) {
            /** @var $item \DirectoryIterator */
            if ($item->isDir() && !$item->isDot()) {
                $this->directory($item->getPathname() . '/');
            }
        }

        return $this;
    }

    /**
     * Rename the dir and mark them as "to remove".
     * Speed up the remove process.
     *
     * @param string $absoluteDirName
     *
     * @return RemoveService
     */
    public function directory(string $absoluteDirName): self
    {
        if (\is_dir($absoluteDirName)) {
            $tempAbsoluteDir = \rtrim($absoluteDirName, '/') . '_' . GeneralUtility::milliseconds() . '/';
            \rename($absoluteDirName, $tempAbsoluteDir);
            $this->removeDirs[] = $tempAbsoluteDir;
        }

        return $this;
    }

    /**
     * Finally remove the dirs.
     */
    public function __destruct()
    {
        foreach ($this->removeDirs as $removeDir) {
            GeneralUtility::rmdir($removeDir, true);
        }
        $this->removeDirs = [];
    }
}

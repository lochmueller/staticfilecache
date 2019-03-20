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
    public function removeFile(string $absoulteFileName): bool
    {
        if (!\is_file($absoulteFileName)) {
            return true;
        }

        return (bool)\unlink($absoulteFileName);
    }

    /**
     * Rename the dir and mark them as "to remove".
     * Speed up the remove process.
     *
     * @param string $absoluteDirName
     *
     * @return RemoveService
     */
    public function softRemoveDir(string $absoluteDirName)
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
    public function removeDirs()
    {
        foreach ($this->removeDirs as $removeDir) {
            GeneralUtility::rmdir($removeDir, true);
        }
        $this->removeDirs = [];
    }
}

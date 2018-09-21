<?php

declare(strict_types=1);
/**
 * AbstractHttpPush.
 */

namespace SFC\Staticfilecache\Service\HttpPush;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * AbstractHttpPush.
 */
abstract class AbstractHttpPush
{
    /**
     * Check if the class can handle the file extension.
     *
     * @param string $fileExtension
     *
     * @return bool
     */
    abstract public function canHandleExtension(string $fileExtension): bool;

    /**
     * Get headers for the current file extension.
     *
     * Array of items with:
     * - path
     * - type
     *
     * @param string $content
     *
     * @return array
     */
    abstract public function getHeaders(string $content): array;

    /**
     * Streamline file paths
     *
     * @param array $paths
     * @return array
     */
    protected function streamlineFilePaths(array $paths): array
    {
        $paths = array_map(function ($path) {
            return GeneralUtility::locationHeaderUrl($path);
        }, $paths);

        $paths = array_filter($paths, function ($path) {
            return GeneralUtility::isOnCurrentHost($path);
        });

        return $paths;
    }

    /**
     * Map the path with the right types
     *
     * @param array $paths
     * @param string $type
     * @return array
     */
    protected function mapPathsWithType(array $paths, string $type): array
    {
        return array_map(function ($item) use ($type) {
            return [
                'path' => $item,
                'type' => $type,
            ];
        }, $paths);
    }
}

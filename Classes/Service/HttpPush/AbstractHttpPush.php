<?php

/**
 * AbstractHttpPush.
 */
declare(strict_types = 1);

namespace SFC\Staticfilecache\Service\HttpPush;

use SFC\Staticfilecache\Service\AbstractService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * AbstractHttpPush.
 */
abstract class AbstractHttpPush extends AbstractService
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
     * Streamline file paths.
     *
     * @param array $paths
     *
     * @return array
     */
    protected function streamlineFilePaths(array $paths): array
    {
        $paths = \array_map(function ($url) {
            if (!GeneralUtility::isValidUrl($url) && ':' !== $url[0]) {
                $url = GeneralUtility::locationHeaderUrl($url);
            }

            return $url;
        }, $paths);

        $paths = \array_filter($paths, function ($path) {
            return GeneralUtility::isOnCurrentHost($path) && ':' !== $path[0];
        });

        $paths = \array_map(function ($url) {
            return '/' . \ltrim(\str_replace(GeneralUtility::getIndpEnv('TYPO3_REQUEST_HOST'), '', $url), '/');
        }, $paths);

        return $paths;
    }

    /**
     * Map the path with the right types.
     * Take care that paths are not used twice.
     *
     * @param array  $paths
     * @param string $type
     *
     * @return array
     */
    protected function mapPathsWithType(array $paths, string $type): array
    {
        return array_values(\array_map(function ($item) use ($type) {
            return [
                'path' => $item,
                'type' => $type,
            ];
        }, \array_unique($paths)));
    }
}

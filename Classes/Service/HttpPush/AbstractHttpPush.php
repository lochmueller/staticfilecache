<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Service\HttpPush;

use SFC\Staticfilecache\Event\HttpPushHeaderEvent;
use TYPO3\CMS\Core\Utility\GeneralUtility;

abstract class AbstractHttpPush
{
    public function __invoke(HttpPushHeaderEvent $event): void
    {
        foreach ($event->getExtensions() as $extension) {
            if ($this->canHandleExtension($extension)) {
                $event->setHeaders(array_merge($event->getHeaders(), $this->getHeaders($event->getContent())));
            }
        }
    }

    /**
     * Check if the class can handle the file extension.
     */
    abstract public function canHandleExtension(string $fileExtension): bool;

    /**
     * Get headers for the current file extension.
     *
     * Array of items with:
     * - path
     * - type
     */
    abstract public function getHeaders(string $content): array;

    /**
     * Streamline file paths.
     */
    protected function streamlineFilePaths(array $paths): array
    {
        $paths = array_map(function ($url) {
            if (!GeneralUtility::isValidUrl($url) && ':' !== $url[0]) {
                $url = GeneralUtility::locationHeaderUrl($url);
            }

            return $url;
        }, $paths);

        $paths = array_filter($paths, fn($path) => GeneralUtility::isOnCurrentHost($path) && ':' !== $path[0]);

        return array_map(fn($url) => '/' . ltrim(str_replace(GeneralUtility::getIndpEnv('TYPO3_REQUEST_HOST'), '', $url), '/'), $paths);
    }

    /**
     * Map the path with the right types.
     * Take care that paths are not used twice.
     */
    protected function mapPathsWithType(array $paths, string $type): array
    {
        return array_values(array_map(function ($item) use ($type) {
            return [
                'path' => $item,
                'type' => $type,
            ];
        }, array_unique($paths)));
    }
}

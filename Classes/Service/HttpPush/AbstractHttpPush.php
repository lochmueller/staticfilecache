<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Service\HttpPush;

use Psr\Http\Message\ServerRequestInterface;
use SFC\Staticfilecache\Event\HttpPushHeaderEvent;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Utility\GeneralUtility;

abstract class AbstractHttpPush
{
    public function __invoke(HttpPushHeaderEvent $event): void
    {
        foreach ($event->getExtensions() as $extension) {
            if ($this->canHandleExtension($extension)) {
                $event->setHeaders(array_merge($event->getHeaders(), $this->getHeaders($event->getContent(), $event->getRequest())));
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
    abstract public function getHeaders(string $content, ServerRequestInterface $request): array;

    /**
     * Streamline file paths.
     */
    protected function streamlineFilePaths(array $paths, ServerRequestInterface $request): array
    {
        $normalizedParams = $request->getAttribute('normalizedParams');
        if (!$normalizedParams instanceof NormalizedParams) {
            return [];
        }

        $paths = array_map(function ($url) use ($request) {
            if (!GeneralUtility::isValidUrl($url) && ':' !== $url[0]) {
                $url = GeneralUtility::locationHeaderUrl($url, $request);
            }

            return $url;
        }, $paths);

        $paths = array_filter($paths, fn($path) => GeneralUtility::isOnCurrentHost($path, $request) && ':' !== $path[0]);

        $requestHost = $normalizedParams->getRequestHost();

        return array_map(fn($url) => '/' . ltrim(str_replace($requestHost, '', $url), '/'), $paths);
    }

    /**
     * Map the path with the right types.
     * Take care that paths are not used twice.
     */
    protected function mapPathsWithType(array $paths, string $type): array
    {
        return array_values(array_map(fn($item) => [
            'path' => $item,
            'type' => $type,
        ], array_unique($paths)));
    }
}

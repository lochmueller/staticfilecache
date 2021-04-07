<?php

/**
 * StyleHttpPush.
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Service\HttpPush;

/**
 * StyleHttpPush.
 */
class StyleHttpPush extends AbstractHttpPush
{
    /**
     * Check if the class can handle the file extension.
     */
    public function canHandleExtension(string $fileExtension): bool
    {
        return 'css' === $fileExtension;
    }

    /**
     * Get headers for the current file extension.
     */
    public function getHeaders(string $content): array
    {
        preg_match_all('/(?<=["\'])[^="\'\\\\]*\.css(\.gzi?p?)?(\?\d*)?(?=["\'])/', $content, $cssFiles);
        $paths = $this->streamlineFilePaths((array) $cssFiles[0]);

        return $this->mapPathsWithType($paths, 'style');
    }
}

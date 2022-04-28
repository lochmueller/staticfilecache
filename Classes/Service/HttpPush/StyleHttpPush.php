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
        if(!preg_match_all('/(?<=href=")(?<src>[^"]+?\.css(\.gzi?p?)?(\?\d+)?)(?=")(?!\smedia="print")/', $content, $cssFiles)) {
            return [];
        }

        $paths = $this->streamlineFilePaths((array) $cssFiles['src']);

        return $this->mapPathsWithType($paths, 'style');
    }
}

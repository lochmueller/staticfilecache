<?php

/**
 * FontHttpPush.
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Service\HttpPush;

/**
 * FontHttpPush.
 */
class FontHttpPush extends AbstractHttpPush
{
    /**
     * Check if the class can handle the file extension.
     */
    public function canHandleExtension(string $fileExtension): bool
    {
        return 'woff' === $fileExtension;
    }

    /**
     * Get headers for the current file extension.
     */
    public function getHeaders(string $content): array
    {
        preg_match_all('/(?<=["\'])[^="\']*\.woff2?\.*\d*\.*(?:gzi?p?)*(?=["\'])/', $content, $fontFiles);
        $paths = $this->streamlineFilePaths((array) $fontFiles[0]);

        return $this->mapPathsWithType($paths, 'font');
    }
}

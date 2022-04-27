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
     * Last checked extension.
     */
    protected ?string $lastExtension;

    /**
     * Fonts extensions.
     */
    private $fontsExtensions = ['woff', 'woff2'];

    /**
     * Check if the class can handle the file extension.
     */
    public function canHandleExtension(string $fileExtension): bool
    {
        $handle = \in_array($fileExtension, $this->fontsExtensions, true);
        if ($handle) {
            $this->lastExtension = $fileExtension;
        }

        return $handle;
    }

    /**
     * Get headers for the current file extension.
     */
    public function getHeaders(string $content): array
    {
        if (null === $this->lastExtension) {
            return [];
        }

        preg_match_all('/(?<=["\'])[^="\']*\.woff2?\.*\d*\.*(?:gzi?p?)*(?=["\'])/', $content, $fontFiles);
        $paths = $this->streamlineFilePaths((array) $fontFiles[0]);

        return $this->mapPathsWithType($paths, 'font');
    }
}

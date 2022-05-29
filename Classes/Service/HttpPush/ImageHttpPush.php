<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Service\HttpPush;

/**
 * ImageHttpPush.
 */
class ImageHttpPush extends AbstractHttpPush
{
    /**
     * Last checked extension.
     */
    protected ?string $lastExtension;

    /**
     * Image extensions.
     */
    protected array $imageExtensions = ['ico', 'png', 'jpg', 'jpeg'];

    /**
     * Check if the class can handle the file extension.
     */
    public function canHandleExtension(string $fileExtension): bool
    {
        $handle = \in_array($fileExtension, $this->imageExtensions, true);
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

        if (!preg_match_all('/(?<=")(?<src>[^"]+?\.'.$this->lastExtension.')(?=")/', $content, $imageFiles)) {
            return [];
        }

        $paths = $this->streamlineFilePaths((array) $imageFiles['src']);

        return $this->mapPathsWithType($paths, 'image');
    }
}

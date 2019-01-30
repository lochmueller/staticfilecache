<?php

/**
 * ImageHttpPush.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Service\HttpPush;

/**
 * ImageHttpPush.
 */
class ImageHttpPush extends AbstractHttpPush
{
    /**
     * Image extensions.
     *
     * @var array
     */
    protected $imageExtensions = ['png', 'jpg', 'jpeg'];

    /**
     * Check if the class can handle the file extension.
     *
     * @param $fileExtension
     *
     * @return bool
     */
    public function canHandleExtension(string $fileExtension): bool
    {
        return \in_array($fileExtension, $this->imageExtensions, true);
    }

    /**
     * Get headers for the current file extension.
     *
     * @param string $content
     *
     * @return array
     */
    public function getHeaders(string $content): array
    {
        \preg_match_all('/(?<=["\'])[^="\']*\.(' . \implode('|', $this->imageExtensions) . ')\.*\d*\.*(?=["\'])/', $content, $imagesFiles);
        $paths = $this->streamlineFilePaths((array)$imagesFiles[0]);

        return $this->mapPathsWithType($paths, 'image');
    }
}

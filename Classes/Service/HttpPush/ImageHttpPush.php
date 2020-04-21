<?php

/**
 * ImageHttpPush.
 */

declare(strict_types=1);

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
     * Last checked extension
     *
     * @var string
     */
    protected $lastExtension;

    /**
     * Check if the class can handle the file extension.
     *
     * @param $fileExtension
     *
     * @return bool
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
     *
     * @param string $content
     *
     * @return array
     */
    public function getHeaders(string $content): array
    {
        if ($this->lastExtension === null) {
            return [];
        }

        \preg_match_all('/(?<=["\'])[^="\']*\.(' . $this->lastExtension . ')\.*\d*\.*(?=["\'])/', $content, $imagesFiles);
        $paths = $this->streamlineFilePaths((array)$imagesFiles[0]);

        return $this->mapPathsWithType($paths, 'image');
    }
}

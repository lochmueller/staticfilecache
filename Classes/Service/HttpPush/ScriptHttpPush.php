<?php

/**
 * ScriptHttpPush.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Service\HttpPush;

/**
 * ScriptHttpPush.
 */
class ScriptHttpPush extends AbstractHttpPush
{
    /**
     * Check if the class can handle the file extension.
     *
     * @param $fileExtension
     *
     * @return bool
     */
    public function canHandleExtension(string $fileExtension): bool
    {
        return 'js' === $fileExtension;
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
        preg_match_all('/(?<=["\'])[^="\']*\.js\.*\d*\.*(?:gzi?p?)[^="\']*(?=["\'])/', $content, $jsFiles);
        $paths = $this->streamlineFilePaths((array)$jsFiles[0]);
        return $this->mapPathsWithType($paths, 'script');
    }
}

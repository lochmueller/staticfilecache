<?php

/**
 * ScriptHttpPush.
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Service\HttpPush;

/**
 * ScriptHttpPush.
 */
class ScriptHttpPush extends AbstractHttpPush
{
    /**
     * Check if the class can handle the file extension.
     */
    public function canHandleExtension(string $fileExtension): bool
    {
        return 'js' === $fileExtension;
    }

    /**
     * Get headers for the current file extension.
     */
    public function getHeaders(string $content): array
    {
        preg_match_all('/src=["\'][^="\'\\\\]*\.js(\.gzi?p?)?(\?\d*)?(?=["\'])/', $content, $jsFiles);

        $res = array_map(function ($item) {
            // skip: src=('|") -> 5 chars
            return substr($item, 5);
        }, (array) $jsFiles['0']);

        $paths = $this->streamlineFilePaths($res);

        return $this->mapPathsWithType($paths, 'script');
    }
}

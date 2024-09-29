<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Service\HttpPush;

/**
 * @author Marcus Förster ; https://github.com/xerc
 */
class SvgHttpPush extends AbstractHttpPush
{
    /**
     * Check if the class can handle the file extension.
     */
    public function canHandleExtension(string $fileExtension): bool
    {
        return 'svg' === $fileExtension;
    }

    /**
     * Get headers for the current file extension.
     */
    public function getHeaders(string $content): array
    {
        if (!preg_match_all('/(?<=")(?<src>[^"]+\.svg)(?:#[\w\-]+)?(?=")/', $content, $svgFiles)) {
            return [];
        }

        $paths = $this->streamlineFilePaths((array) $svgFiles['src']);

        return $this->mapPathsWithType($paths, 'image');
    }
}

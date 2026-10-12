<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Service\HttpPush;

use Psr\Http\Message\ServerRequestInterface;

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
    public function getHeaders(string $content, ServerRequestInterface $request): array
    {
        if (!preg_match_all('/(?<=src=")(?<src>[^"]+?\.js(\.gzi?p?)?(\?\d+)?)(?=")/', $content, $jsFiles)) {
            return [];
        }

        $paths = $this->streamlineFilePaths($jsFiles['src'], $request);

        return $this->mapPathsWithType($paths, 'script');
    }
}

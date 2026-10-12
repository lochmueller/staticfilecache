<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Service\HttpPush;

use Psr\Http\Message\ServerRequestInterface;

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
     *
     * @return list<array{path: string, type: string}>
     */
    public function getHeaders(string $content, ServerRequestInterface $request): array
    {
        if (!preg_match_all('/(?<=href=")(?<src>[^"]+?\.css(\.gzi?p?)?(\?\d+)?)(?=")(?!\smedia="print")/', $content, $cssFiles)) {
            return [];
        }

        $paths = $this->streamlineFilePaths((array) $cssFiles['src'], $request);

        return $this->mapPathsWithType($paths, 'style');
    }
}

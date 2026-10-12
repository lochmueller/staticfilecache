<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Service\HttpPush;

use Psr\Http\Message\ServerRequestInterface;

class ImageHttpPush extends AbstractHttpPush
{
    /**
     * Last checked extension.
     */
    protected ?string $lastExtension = null;

    /**
     * Image extensions.
     *
     * @var string[]
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
     *
     * @return list<array{path: string, type: string}>
     */
    public function getHeaders(string $content, ServerRequestInterface $request): array
    {
        if (null === $this->lastExtension) {
            return [];
        }

        if (!preg_match_all('/(?<=")(?<src>[^"]+?\.' . $this->lastExtension . ')(?=")/', $content, $imageFiles)) {
            return [];
        }

        $paths = $this->streamlineFilePaths((array) $imageFiles['src'], $request);

        return $this->mapPathsWithType($paths, 'image');
    }
}

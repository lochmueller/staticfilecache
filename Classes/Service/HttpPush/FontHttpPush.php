<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Service\HttpPush;

use Psr\Http\Message\ServerRequestInterface;

class FontHttpPush extends AbstractHttpPush
{
    /**
     * Last checked extension.
     */
    protected ?string $lastExtension = null;

    /**
     * Fonts extensions.
     *
     * @var string[]
     */
    private array $fontsExtensions = ['woff', 'woff2'];


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
     *
     * @return list<array{path: string, type: string}>
     */
    public function getHeaders(string $content, ServerRequestInterface $request): array
    {
        if (null === $this->lastExtension) {
            return [];
        }

        if (!preg_match_all('/(?<=")(?<src>[^"]+?\.' . $this->lastExtension . ')(?=")/', $content, $fontFiles)) {
            return [];
        }

        $paths = $this->streamlineFilePaths((array) $fontFiles['src'], $request);

        return $this->mapPathsWithType($paths, 'font');
    }
}

<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Service\InlineAssets;

/**
 * @author Marcus Förster ; https://github.com/xerc
 */
class InlineImages extends AbstractInlineAssets
{
    /**
     * Image extensions.
     */
    private array $imageExtensions = ['png', 'jpg', 'jpeg'];

    /**
     * Check if the class can handle the file extension.
     */
    public function canHandleExtension(string $fileExtension): bool
    {
        return \in_array($fileExtension, $this->imageExtensions, true);
    }

    /**
     * Replace all matching Files within given HTML.
     */
    public function replaceInline(string $content): string
    {
        return preg_replace_callback('/(?<=<img src=")(?<src>\/.+?\.(?<ext>' . implode('|', $this->imageExtensions) . '))(?=")/', fn(array $match): string => $this->parseAsset($match), $content);
    }
}

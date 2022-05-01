<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Service\InlineAssets;

/**
 * Class InlineFavIcon.
 *
 * @author Marcus Förster ; https://github.com/xerc
 */
class InlineFavIcon extends AbstractInlineAssets
{
    /**
     * FavIcon extensions.
     */
    private $favIconExtensions = ['svg', 'ico', 'png'];

    /**
     * Check if the class can handle the file extension.
     */
    public function canHandleExtension(string $fileExtension): bool
    {
        return \in_array($fileExtension, $this->favIconExtensions, true);
    }

    /**
     * Replace all matching Files within given HTML
     */
    public function replaceInline(string $content): string
    {
        if (false === preg_match('/<link rel=".*?icon.*?" href="(?<src>\/.+?\.(?<ext>'.implode('|', $this->favIconExtensions).'))"[^>]*>/', $content, $match)) {
            return $content;
        }

        return str_replace($match[0], '<link rel="icon" href=\''.$this->parseAsset($match).'\'>', $content);
    }
}

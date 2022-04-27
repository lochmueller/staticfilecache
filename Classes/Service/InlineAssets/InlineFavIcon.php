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

    public function replaceInline(string $content): string
    {
        if(false === preg_match('/<link.+?rel=(").*?icon.*?\1\shref=\1(?<src>.+?\.(?<ext>'.implode('|',$fileExtensions).')))\1[^>]*>/', $content, $match)) {
            return $content;
        }

        return str_replace($match['src'],$this->parseAsset($match),$content);
    }
}

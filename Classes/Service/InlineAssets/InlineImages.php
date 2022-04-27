<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Service\InlineAssets;

/**
 * Class InlineImages.
 *
 * @author Marcus Förster ; https://github.com/xerc
 */
class InlineImages extends AbstractInlineAssets
{
    /**
     * Assets extensions.
     */
    private $imageExtensions = ['png', 'jpg', 'jpeg'];

    /**
     * Check if the class can handle the file extension.
     */
    public function canHandleExtension(string $fileExtension): bool
    {
        return \in_array($fileExtension, $this->imageExtensions, true);
    }

    public function replaceInline(string $content): string
    {
        $content = preg_replace_callback('/(?<=<img\ssrc=(["\']))(?<src>.+?\.(?<ext>'.implode('|',$this->imageExtensions).'))(?=\1)/', function (array $match): string
        {
            return $this->parseAsset($match);

        }, $content);

        return $content;
    }
}

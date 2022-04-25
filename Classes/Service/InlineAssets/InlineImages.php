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
    private $fileExtensions = ['png', 'jpg', 'jpeg'];

    /**
     * Check if the class can handle the file extension.
     */
    public function canHandleExtension(string $fileExtension): bool
    {
        return \in_array($fileExtension, $this->imageExtensions, true);
    }

    public function replaceInline(string $content): string
    {
        $content = preg_replace_callback('/(?<=<img\ssrc=(["\']))(?<src>.+?\.(?<ext>'.implode('|',$this->fileExtensions).'))(?=\1)/', function (array $match): string {

            $path = $this->streamlineFilePaths((array) $match['path'])[0];
            if(!file_exists($path)) {// CHECK @ streamlineFilePaths ?!
                return $match[0];
            }

            if(filesize($path) >= $configurationService->get('inlineImages')) {
              return $match[0];
            }

            $file = file_get_contents($path);
            if(empty($file)) {// CHECK ; needet?!
                return $match[0];
            }

            return $this->parseAsset($match);

        }, $content);

        return $content;
    }
}

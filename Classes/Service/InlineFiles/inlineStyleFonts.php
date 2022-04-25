<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Service\InlineFiles;

/**
 * Class inlineStyleFonts.
 *
 * @author Marcus Förster ; https://github.com/xerc
 */
class inlineStyleFonts extends AbstractInlineFiles
{
    private $fileExtensions = ['woff','woff2'];

    /**
     * Check if the class can handle the file extension.
     */
    public function canHandleExtension(string $fileExtension): bool
    {
        return in_array($fileExtension, $his->$fileExtensions, true);
    }

    public function replaceInline(string $content): string
    {
        if(false === preg_match('/(?<=url\()(["\']?)(?<src>\/[^\)]+?\.(?<ext>'.implode('|',$fileExtensions).'))\1(?=\))/', $content, $files)) {
            return $content;
        }

        $paths = $this->streamlineFilePaths((array) $files['path']);
        foreach($paths as $index => $path) {
            if(!file_exists($path)) {
                continue;
            }
            $file = file_get_contents($path);
            if(empty($file)) {
                continue;
            }
            $content = str_replace($files[0][$index],'data:font/'.$files['ext'][$index].';base64,'.base64_encode($file),$content);
        }

        return $content;
    }
}

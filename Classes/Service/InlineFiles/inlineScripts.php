<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Service\InlineFiles;

/**
 * Class inlineStyles.
 *
 * @author Marcus Förster ; https://github.com/xerc
 */
class inlineStyles extends AbstractInlineFiles
{
    /**
     * Check if the class can handle the file extension.
     */
    public function canHandleExtension(string $fileExtension): bool
    {
        return 'js' === $fileExtension;
    }

    public function replaceInline(string $content): string
    {
        if(false === preg_match('/<script.*?src=(["\'])(?<path>.+?\.js)(\.gzi?p?)?(\?\d*)?\1[^>]*>(?=<\/script>)/', $content, $files)) {
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
            $content = str_replace($files[0][$index],'<script>'.$file,$content);
        }

        return preg_replace('<\/script>\s*<script>','',$content);// cleanup
    }
}

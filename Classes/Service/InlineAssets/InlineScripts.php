<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Service\InlineAssets;

/**
 * Class InlineScripts.
 *
 * @author Marcus Förster ; https://github.com/xerc
 */
class InlineScripts extends AbstractInlineAssets
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
        if(false === preg_match_all('/<script.*?src=(["\'])(?<path>.+?)(\.\d+)?\.js(\.gzi?p?)?(\?\d*)?\1[^>]*>(?=<\/script>)/', $content, $matches))
        {
            return $content;
        }

        $paths = $this->streamlineFilePaths((array) $matches['path']);
        foreach($paths as $index => $path)
        {
            $file = file_get_contents($this->sitePath.$path.'.js');

            $content = str_replace($matches[0][$index],'<script>'.rtrim($file),$content);
        }

        return preg_replace('/<\/script>\s*<script>/','',$content);// cleanup
    }
}

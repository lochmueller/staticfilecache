<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Service\InlineAssets;

/**
 * Class InlineStyles.
 *
 * @author Marcus Förster ; https://github.com/xerc
 */
class InlineStyles extends AbstractInlineAssets
{
    /**
     * Image extensions.
     */
    private $imageExtensions = ['ico', 'png', 'jpg', 'jpeg'];

    /**
     * Fonts extensions.
     */
    private $fontExtensions  = ['woff2'];

    /**
     * Check if the class can handle the file extension.
     */
    public function canHandleExtension(string $fileExtension): bool
    {
        return 'css' === $fileExtension;
    }

    public function replaceInline(string $content): string
    {
        if(false === preg_match_all('/<link rel="stylesheet" href=(["\'])(?<path>.+?)(\.\d+)?\.css(\.gzi?p?)?(\?\d*)?\1(?!\smedia=\1print\1)[^>]*>/', $content, $matches))
        {
            return $content;
        }

        $paths = $this->streamlineFilePaths((array) $matches['path']);
        foreach($paths as $index => $path)
        {
            $file = file_get_contents($this->sitePath.$path.'.css');

            if($this->configurationService->get('inlineStyleAssets'))
            {
                $file = $this->includeAssets('/(?<=url\()(["\']?)(?<src>[^\)]+?\.(?<ext>'.implode('|',array_merge($this->imageExtensions,$this->fontExtensions)).'))\1(?=\))/', $file);
            }

            $content = str_replace($matches[0][$index],'<style>'.rtrim($file).'</style>',$content);
        }

        return preg_replace('/<\/style>\s*<style>/','',$content);// cleanup
    }
}

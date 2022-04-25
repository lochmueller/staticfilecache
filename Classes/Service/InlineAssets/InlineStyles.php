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
    private $imagesExtensions = ['svg', 'ico', 'png', 'jpg', 'jpeg'];

    /**
     * Fonts extensions.
     */
    private $fontsExtensions  = ['woff', 'woff2'];

    /**
     * Check if the class can handle the file extension.
     */
    public function canHandleExtension(string $fileExtension): bool
    {
        return 'css' === $fileExtension;
    }

    public function replaceInline(string $content): string
    {
        if(false === preg_match_all('/<link rel="stylesheet" href=(["\'])(?<path>.+?\.css)(\.gzi?p?)?(\?\d*)?\1(?!\smedia=\1print\1)[^>]*>/', $content, $matches)) {
            return $content;
        }

        $paths = $this->streamlineFilePaths((array) $matches['path']);
        foreach($paths as $index => $path) {

            if(!file_exists($path)) {// CHECK @ streamlineFilePaths ?!
                continue;
            }
            $file = file_get_contents($path);
            if(empty($file)) {// CHECK ; needet?!
                continue;
            }

            if($this->configurationService->get('inlineStyleAssets')) {
                $file = $this->includeAssets('/(?<=url\()(["\']?)(?<src>[^\)]+?\.(?<ext>'.implode('|',$this->imagesExtensions+$this->fontsExtensions).'))\1(?=\))/', $file);
            }

            $content = str_replace($matches[0][$index],'<style>'.$file.'</style>',$content);
        }

        return preg_replace('/</style>\s*<style>/','',$content);// cleanup
    }

}

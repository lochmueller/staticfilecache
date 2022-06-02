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
     * Font extensions.
     */
    private $fontExtensions = ['woff', 'woff2'];

    /**
     * Check if the class can handle the file extension.
     */
    public function canHandleExtension(string $fileExtension): bool
    {
        return 'css' === $fileExtension;
    }

    /**
     * Replace all matching Files within given HTML.
     */
    public function replaceInline(string $content): string
    {
        if (!preg_match_all('/<link rel="stylesheet".+?href="(?<path>\/.+?)(\.\d+)?\.css(\.gzi?p?)?(\?\d*)?"(?!\smedia="print")[^>]*>/', $content, $matches)) {
            return $content;
        }

        foreach ($matches['path'] as $index => $path) {
            $fileSrc = file_get_contents($this->sitePath.$path.'.css');

            if (!empty($this->configurationService->get('inlineStyleAssets'))) {
                $fileExtensions = preg_grep('/'.str_replace(',', '|', $this->configurationService->get('inlineStyleAssets')).'/', array_merge($this->imageExtensions, $this->fontExtensions));
                if (\is_array($fileExtensions)) {
                    $fileSrc = $this->includeAssets('/(?<=url\()(["\']?)(?<src>\/[^\)]+?\.(?<ext>'.implode('|', array_values($fileExtensions)).'))\1(?=\))/', $fileSrc);
                }
            }

            if ($this->configurationService->get('inlineStyleMinify')) {
                $fileSrc = preg_replace('/\v+/', '', $fileSrc); // remove line-breaks
                $fileSrc = preg_replace('/\h+/', ' ', $fileSrc); // shrink whitespace

                $fileSrc = preg_replace('/\/\*.*?\*\//', '', $fileSrc); // remove multi-line comments
                $fileSrc = preg_replace('/ *([{;:>~}]) */', '$1', $fileSrc); // remove no-req. spaces
                $fileSrc = preg_replace('/;(?=})/', '', $fileSrc); // shorten declaration endings
            }

            $content = str_replace($matches[0][$index], '<style>'.rtrim($fileSrc).'</style>', $content);
        }

        return preg_replace('/<\/style>\s*<style>/', '', $content); // cleanup
    }
}

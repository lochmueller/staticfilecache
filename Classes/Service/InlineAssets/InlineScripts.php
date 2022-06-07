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

    /**
     * Replace all matching Files within given HTML.
     */
    public function replaceInline(string $content): string
    {
        if (!preg_match_all('/<script(\sasync)? src="(?<path>\/.+?)(\.\d+)?\.js(\.gzi?p?)?(\?\d*)?"[^>]*>(?=<\/script>)/', $content, $matches)) {
            return $content;
        }

        foreach ($matches['path'] as $index => $path) {
            $fileSrc = file_get_contents($this->sitePath.$path.'.js');

            if ($this->configurationService->get('inlineScriptMinify')) {
                $fileSrc = preg_replace('/^\s*\/\/.*$/m', '', $fileSrc); // remove single-line comments
                // if (!preg_match('/(?<![\'":])\/\//', $fileSrc)) { // RISKY; https?://|"//|'//
                //     $fileSrc = mb_eregi_replace('/\v+/', '', $fileSrc); // remove line-breaks
                // }
                $fileSrc = mb_eregi_replace('/\h+/', ' ', $fileSrc); // shrink whitespace

                $fileSrc = preg_replace('/\/\*.*?\*\//s', '', $fileSrc); // remove multi-line comments
                $fileSrc = preg_replace('/ *([(?&:,=*+\-\/)]) */', '$1', $fileSrc); // remove no-req. spaces

                $fileSrc = preg_replace('/(?<={)\s+|(?<=\)|>)\s+(?={)/', '', $fileSrc); // remove function "start"
                $fileSrc = preg_replace('/;(?=})|(?<=});\s/', '', $fileSrc); // shorten function "end"
            }

            $content = str_replace($matches[0][$index], '<script>'.rtrim($fileSrc), $content);
        }

        return preg_replace('/<\/script>\s*<script>/', '', $content); // cleanup
    }
}

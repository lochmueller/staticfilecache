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
     * Replace all matching Files within given HTML
     */
    public function replaceInline(string $content): string
    {
        if (false === preg_match_all('/<script(\sasync)? src="(?<path>\/.+?)(\.\d+)?\.js(\.gzi?p?)?(\?\d*)?"[^>]*>(?=<\/script>)/', $content, $matches)) {
            return $content;
        }

        foreach ($matches['path'] as $index => $path) {
            $fileSrc = file_get_contents($this->sitePath.$path.'.js');

            $content = str_replace($matches[0][$index], '<script>'.rtrim($fileSrc), $content);
        }

        return preg_replace('/<\/script>\s*<script>/', '', $content); // cleanup
    }
}

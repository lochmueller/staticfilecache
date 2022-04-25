<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Service\InlineFiles;

/**
 * Class inlineFavIcon.
 *
 * @author Marcus Förster ; https://github.com/xerc
 */
class inlineFavIcon extends AbstractInlineFiles
{
    private $fileExtensions = ['svg','ico','png'];

    /**
     * Check if the class can handle the file extension.
     */
    public function canHandleExtension(string $fileExtension): bool
    {
        return in_array($fileExtension, $his->$fileExtensions, true);
    }

    public function replaceInline(string $content): string
    {
        if(false === preg_match('/<link.+?rel=(").*?icon.*?\1\shref=\1(?<href>.+?\.(?<ext>'.implode('|',$fileExtensions).')))\1[^>]*>/', $content, $files)) {
            return $content;
        }
        if(1 != count($files['href'])) {
            return $content;
        }

        $path = $this->streamlineFilePaths((array) $files['href'][0])[0];
        if(!file_exists($path)) {
            return $content;
        }

        $file = file_get_contents($path);
        if(empty($file)) {
            return $content;
        }

        switch($files['ext'][0]) {
            case 'svg':
                $type = 'svg+xml;utf8';
                $file = str_replace('"','\'',$file);// quotes

                $file = preg_replace('/\v/','',$file);// line-breaks
                $file = preg_replace('/#([a-f0-9]{3,6})/','%23$1',$file);// fix:color
            break;
            case 'ico':
                $type = 'x-icon;base64';
                $file = base64_encode($file);
            break;
            case 'png':
                $type = 'png;base64';
                $file = base64_encode($file);
            break;
        }
        return str_replace($files['href'][0],"data:image/$type,$file",$content);
    }
}

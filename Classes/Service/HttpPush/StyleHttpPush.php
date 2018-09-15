<?php
/**
 * StyleHttpPush
 */

namespace SFC\Staticfilecache\Service\HttpPush;

/**
 * StyleHttpPush
 */
class StyleHttpPush extends AbstractHttpPush
{

    /**
     * Check if the class can handle the file extension
     *
     * @param $fileExtension
     * @return bool
     */
    public function canHandleExtension(string $fileExtension): bool
    {
        return $fileExtension === 'css';
    }

    /**
     * Get headers for the current file extension
     *
     * @param string $content
     * @return array
     */
    public function getHeaders(string $content): array
    {

        // @todo add css fetch and build header

        // preg_match_all('/(?<=["\'])[^="\']*\.css\.*\d*\.*(?:gzi?p?)*(?=["\'])/', $content, $cssFiles);


        return [];
    }
}

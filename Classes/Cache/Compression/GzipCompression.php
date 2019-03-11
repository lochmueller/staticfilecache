<?php
/**
 * Abstract Rule.
 */
declare(strict_types = 1);

namespace SFC\Staticfilecache\Cache\Compression;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Abstract Rule.
 */
class GzipCompression extends AbstractCompression
{
    /**
     * Compress.
     *
     * @param string $fileName
     * @param mixed  $data
     */
    public function compress(string $fileName, $data)
    {
        $gzipFileName = $fileName . '.gz';

        // If file already exists, we assume, that it was already written by another Slot
        if (!\file_exists($gzipFileName)) {
            $contentGzip = \gzencode($data, $this->getCompressionLevel());
            if ($contentGzip) {
                GeneralUtility::writeFile($gzipFileName, $contentGzip);
            }
        }
    }
}

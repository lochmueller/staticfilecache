<?php
/**
 * Abstract Rule.
 */
declare(strict_types = 1);
namespace SFC\Staticfilecache\Cache\Compression;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * Abstract Rule.
 */
class GzipCompression
{

    /**
     * The default compression level.
     */
    const DEFAULT_COMPRESSION_LEVEL = 3;

    public function compress($fileName, $data)
    {
        $gzipFileName = $fileName . '.gz';

        // If file already exists, we assume, that it was already written by another Slot
        if (!file_exists($gzipFileName)) {
            $contentGzip = \gzencode($data, $this->getCompressionLevel());
            if ($contentGzip) {
                GeneralUtility::writeFile($gzipFileName, $contentGzip);
            }
        }
    }

    /**
     * Get compression level.
     *
     * @return int
     */
    protected function getCompressionLevel(): int
    {
        $level = self::DEFAULT_COMPRESSION_LEVEL;
        if (isset($GLOBALS['TYPO3_CONF_VARS']['FE']['compressionLevel'])) {
            $level = (int) $GLOBALS['TYPO3_CONF_VARS']['FE']['compressionLevel'];
        }
        if (!MathUtility::isIntegerInRange($level, 1, 9)) {
            $level = self::DEFAULT_COMPRESSION_LEVEL;
        }

        return $level;
    }
}

<?php
/**
 * AbstractCompression.
 */
declare(strict_types = 1);

namespace SFC\Staticfilecache\Cache\Compression;

use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * AbstractCompression.
 */
abstract class AbstractCompression
{
    /**
     * The default compression level.
     */
    const DEFAULT_COMPRESSION_LEVEL = 3;

    /**
     * Get frontend compression level.
     * The value is between 1 (low) and 9 (high).
     *
     * @return int
     */
    protected function getCompressionLevel(): int
    {
        $level = self::DEFAULT_COMPRESSION_LEVEL;
        if (isset($GLOBALS['TYPO3_CONF_VARS']['FE']['compressionLevel'])) {
            $level = (int)$GLOBALS['TYPO3_CONF_VARS']['FE']['compressionLevel'];
        }

        return MathUtility::forceIntegerInRange($level, 1, 9, self::DEFAULT_COMPRESSION_LEVEL);
    }
}

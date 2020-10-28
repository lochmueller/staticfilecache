<?php
/**
 * GzipGenerator.
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Generator;

use Psr\Http\Message\ResponseInterface;
use SFC\Staticfilecache\Service\RemoveService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * GzipGenerator.
 */
class GzipGenerator extends AbstractGenerator
{
    /**
     * The default compression level.
     */
    public const DEFAULT_COMPRESSION_LEVEL = 3;

    /**
     * Generate file.
     */
    public function generate(string $entryIdentifier, string $fileName, ResponseInterface $response, int $lifetime): void
    {
        $contentGzip = gzencode((string) $response->getBody(), $this->getCompressionLevel());
        if ($contentGzip) {
            GeneralUtility::writeFile($fileName.'.gz', $contentGzip);
        }
    }

    /**
     * Remove file.
     */
    public function remove(string $entryIdentifier, string $fileName): void
    {
        $removeService = GeneralUtility::makeInstance(RemoveService::class);
        $removeService->file($fileName.'.gz');
    }

    /**
     * Get frontend compression level.
     * The value is between 1 (low) and 9 (high).
     */
    protected function getCompressionLevel(): int
    {
        $level = self::DEFAULT_COMPRESSION_LEVEL;
        if (isset($GLOBALS['TYPO3_CONF_VARS']['FE']['compressionLevel'])) {
            $level = (int) $GLOBALS['TYPO3_CONF_VARS']['FE']['compressionLevel'];
        }

        return MathUtility::forceIntegerInRange($level, 1, 9, self::DEFAULT_COMPRESSION_LEVEL);
    }
}

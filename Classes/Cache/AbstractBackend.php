<?php
/**
 * General Cache functions for Static File Cache
 *
 * @package SFC\NcStaticfilecache\Cache
 * @author  Tim Lochmüller
 */

namespace SFC\NcStaticfilecache\Cache;

use SFC\NcStaticfilecache\Configuration;
use TYPO3\CMS\Core\Cache\Backend\Typo3DatabaseBackend;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * General Cache functions for Static File Cache
 *
 * @author Tim Lochmüller
 */
class AbstractBackend extends Typo3DatabaseBackend
{

    /**
     * The default compression level
     */
    const DEFAULT_COMPRESSION_LEVEL = 3;

    /**
     * Configuration
     *
     * @var Configuration
     */
    protected $configuration;

    /**
     * Constructs this backend
     *
     * @param string $context FLOW3's application context
     * @param array $options Configuration options - depends on the actual backend
     */
    public function __construct($context, array $options = [])
    {
        parent::__construct($context, $options);
        $this->configuration = GeneralUtility::makeInstance(Configuration::class);
    }

    /**
     * Get compression level
     *
     * @return int
     */
    protected function getCompressionLevel()
    {
        $level = isset($GLOBALS['TYPO3_CONF_VARS']['FE']['compressionLevel']) ? (int)$GLOBALS['TYPO3_CONF_VARS']['FE']['compressionLevel'] : self::DEFAULT_COMPRESSION_LEVEL;
        if (!MathUtility::isIntegerInRange($level, 1, 9)) {
            $level = self::DEFAULT_COMPRESSION_LEVEL;
        }
        return $level;
    }

    /**
     * Get the real life time
     *
     * @param int $lifetime
     *
     * @return int
     */
    protected function getRealLifetime($lifetime)
    {
        if (is_null($lifetime)) {
            $lifetime = $this->defaultLifetime;
        }
        if ($lifetime === 0 || $lifetime > $this->maximumLifetime) {
            $lifetime = $this->maximumLifetime;
        }
        return $lifetime;
    }

    /**
     * Get the database connection
     *
     * @return DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }
}

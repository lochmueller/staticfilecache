<?php
/**
 * General Cache functions for Static File Cache
 *
 * @author  Tim Lochmüller
 */
declare(strict_types=1);

namespace SFC\Staticfilecache\Cache;

use SFC\Staticfilecache\Service\ConfigurationService;
use TYPO3\CMS\Core\Cache\Backend\Typo3DatabaseBackend;
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
     * @var ConfigurationService
     */
    protected $configuration;

    /**
     * Constructs this backend
     *
     * @param string $context application context
     * @param array $options Configuration options - depends on the actual backend
     */
    public function __construct($context, array $options = [])
    {
        parent::__construct($context, $options);
        $this->configuration = GeneralUtility::makeInstance(ConfigurationService::class);
    }

    /**
     * Get compression level
     *
     * @return int
     */
    protected function getCompressionLevel(): int
    {
        $level = self::DEFAULT_COMPRESSION_LEVEL;
        if (isset($GLOBALS['TYPO3_CONF_VARS']['FE']['compressionLevel'])) {
            $level = (int)$GLOBALS['TYPO3_CONF_VARS']['FE']['compressionLevel'];
        }
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
    protected function getRealLifetime($lifetime): int
    {
        if (is_null($lifetime)) {
            $lifetime = $this->defaultLifetime;
        }
        if ($lifetime === 0 || $lifetime > $this->maximumLifetime) {
            $lifetime = $this->maximumLifetime;
        }
        return (int)$lifetime;
    }
}

<?php
/**
 * Handle extension and TS configuration
 *
 * @author  Tim Lochmüller
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Service;

/**
 * Handle extension and TS configuration
 */
class ConfigurationService extends AbstractService
{

    /**
     * Current configuration
     *
     * @var array
     */
    protected $configuration = [];

    /**
     * Build up the configuration
     */
    public function __construct()
    {
        if (isset($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['staticfilecache'])) {
            $extensionConfig = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['staticfilecache']);
            if (is_array($extensionConfig)) {
                $this->configuration = array_merge($this->configuration, $extensionConfig);
            }
        }
        if (is_object($GLOBALS['TSFE']) && isset($GLOBALS['TSFE']->tmpl->setup['tx_staticfilecache.']) && is_array($GLOBALS['TSFE']->tmpl->setup['tx_staticfilecache.'])) {
            $this->configuration = array_merge(
                $this->configuration,
                $GLOBALS['TSFE']->tmpl->setup['tx_staticfilecache.']
            );
        }
    }

    /**
     * Get the configuration
     *
     * @param string $key
     *
     * @return null|mixed
     */
    public function get(string $key)
    {
        $result = null;
        if (isset($this->configuration[$key])) {
            $result = $this->configuration[$key];
        } elseif (isset($GLOBALS['TSFE']->config['config']['tx_staticfilecache.'][$key])) {
            $result = $GLOBALS['TSFE']->config['config']['tx_staticfilecache.'][$key];
        }
        return $result;
    }

    /**
     * Get the configuration as bool
     *
     * @param string $key
     * @return bool
     */
    public function getBool(string $key)
    {
        return (bool)$this->get($key);
    }
}

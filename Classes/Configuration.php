<?php
/**
 * Handle extension and TS configuration
 *
 * @package SFC\NcStaticfilecache
 * @author  Tim Lochmüller
 */

namespace SFC\NcStaticfilecache;

use TYPO3\CMS\Core\SingletonInterface;

/**
 * Handle extension and TS configuration
 *
 * @author Tim Lochmüller
 */
class Configuration implements SingletonInterface
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
        if (isset($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['nc_staticfilecache'])) {
            $extensionConfig = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['nc_staticfilecache']);
            if (is_array($extensionConfig)) {
                $this->configuration = array_merge($this->configuration, $extensionConfig);
            }
        }
        if (isset($GLOBALS['TSFE']->tmpl->setup['tx_ncstaticfilecache.']) && is_array($GLOBALS['TSFE']->tmpl->setup['tx_ncstaticfilecache.'])) {
            $this->configuration = array_merge($this->configuration,
                $GLOBALS['TSFE']->tmpl->setup['tx_ncstaticfilecache.']);
        }
    }

    /**
     * Get the configuration
     *
     * @param string $key
     *
     * @return null|mixed
     */
    public function get($key)
    {
        $result = null;
        if (isset($this->configuration[$key])) {
            $result = $this->configuration[$key];
        } elseif (isset($GLOBALS['TSFE']->config['config']['tx_staticfilecache.'][$key])) {
            $result = $GLOBALS['TSFE']->config['config']['tx_staticfilecache.'][$key];
        }
        return $result;
    }
}

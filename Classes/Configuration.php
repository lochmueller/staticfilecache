<?php
/**
 * Handle extension and TS configuration
 *
 * @author  Tim Lochmüller
 */

namespace SFC\Staticfilecache;

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
        if (isset($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['staticfilecache'])) {
            $extensionConfig = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['staticfilecache']);
            if (is_array($extensionConfig)) {
                $this->configuration = array_merge($this->configuration, $extensionConfig);
            }
        }
        if (isset($GLOBALS['TSFE']->tmpl->setup['tx_staticfilecache.']) && is_array($GLOBALS['TSFE']->tmpl->setup['tx_staticfilecache.'])) {
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

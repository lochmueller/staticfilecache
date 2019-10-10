<?php

/**
 * Handle extension and TS configuration.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Service;

use SFC\Staticfilecache\Configuration;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Handle extension and TS configuration.
 */
class ConfigurationService extends AbstractService
{
    /**
     * Current configuration.
     *
     * @var array
     */
    protected $configuration = [];

    /**
     * Build up the configuration.
     *
     * @throws \Exception
     */
    public function __construct()
    {
        $extensionConfig = (array)GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('staticfilecache');
        if (!array_key_exists('validHtaccessHeaders', $extensionConfig)) {
            throw new \Exception('It seams your extension configuration stored in the LocalConfiguration is old. Please save the configuration of the StaticFileCache extension in the extension manager or deployment process.');
        }
        $this->configuration = \array_merge($this->configuration, $extensionConfig);

        if (\is_object($GLOBALS['TSFE']) && isset($GLOBALS['TSFE']->tmpl->setup['tx_staticfilecache.']) && \is_array($GLOBALS['TSFE']->tmpl->setup['tx_staticfilecache.'])) {
            $this->configuration = \array_merge(
                $this->configuration,
                $GLOBALS['TSFE']->tmpl->setup['tx_staticfilecache.']
            );
        }
    }

    /**
     * Get the configuration.
     *
     * @param string $key
     *
     * @return string|null
     */
    public function get(string $key)
    {
        $result = null;
        if (isset($this->configuration[$key])) {
            $result = (string)$this->configuration[$key];
        } elseif (isset($GLOBALS['TSFE']->config['config']['tx_staticfilecache.'][$key])) {
            $result = (string)$GLOBALS['TSFE']->config['config']['tx_staticfilecache.'][$key];
        }

        return $result;
    }

    /**
     * Get backend display mode.
     *
     * @return string
     */
    public function getBackendDisplayMode(): string
    {
        $backendDisplayMode = $this->get('backendDisplayMode');
        $validModes = ['current', 'childs', 'both'];
        if (!\in_array($backendDisplayMode, $validModes, true)) {
            $backendDisplayMode = 'current';
        }

        return $backendDisplayMode;
    }

    /**
     * Get the configuration as bool.
     *
     * @param string $key
     *
     * @return bool
     */
    public function isBool(string $key): bool
    {
        return (bool)$this->get($key);
    }
}

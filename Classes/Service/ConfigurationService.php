<?php

/**
 * Handle extension and TS configuration.
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Service;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Handle extension and TS configuration.
 */
class ConfigurationService extends AbstractService
{
    /**
     * Current configuration.
     */
    protected array $configuration = [];

    /**
     * Overrides.
     */
    protected array $overrides = [];

    /**
     * Build up the configuration.
     *
     * @throws \Exception
     */
    public function __construct()
    {
        $extensionConfig = (array) GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('staticfilecache');
        if (!\array_key_exists('validHtaccessHeaders', $extensionConfig)) {
            throw new \Exception('It seams your extension configuration stored in the LocalConfiguration is old. Please save the configuration of the StaticFileCache extension in the extension manager or deployment process.');
        }
        $this->configuration = array_merge($this->configuration, $extensionConfig);

        if (\is_object($GLOBALS['TSFE'] ?? null) && isset($GLOBALS['TSFE']->tmpl->setup['tx_staticfilecache.']) && \is_array($GLOBALS['TSFE']->tmpl->setup['tx_staticfilecache.'])) {
            $this->configuration = array_merge(
                $this->configuration,
                $GLOBALS['TSFE']->tmpl->setup['tx_staticfilecache.']
            );
        }
    }

    /**
     * Get the configuration for the given key.
     */
    public function get(string $key): ?string
    {
        $result = null;
        if (\array_key_exists($key, $this->overrides)) {
            $result = (string) $this->overrides[$key];
        } elseif (isset($this->configuration[$key])) {
            $result = (string) $this->configuration[$key];
        } elseif (isset($GLOBALS['TSFE']->config['config']['tx_staticfilecache.'][$key])) {
            $result = (string) $GLOBALS['TSFE']->config['config']['tx_staticfilecache.'][$key];
        }

        return $result;
    }

    /**
     * Override a value in execution context.
     */
    public function override(string $key, string $value): void
    {
        $this->overrides[$key] = $value;
    }

    /**
     * Remove the override if exists.
     */
    public function reset(string $key): void
    {
        if (\array_key_exists($key, $this->overrides)) {
            unset($this->overrides[$key]);
        }
    }

    /**
     * Get the configuration.
     */
    public function getAll(): array
    {
        return $this->configuration;
    }

    /**
     * Get backend display mode.
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
     */
    public function isBool(string $key): bool
    {
        return (bool) $this->get($key);
    }
}

<?php

declare(strict_types=1);

namespace SFC\Staticfilecache;

use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use SFC\Staticfilecache\Cache\RemoteFileBackend;
use SFC\Staticfilecache\Cache\StaticFileBackend;
use SFC\Staticfilecache\Cache\UriFrontend;
use SFC\Staticfilecache\Hook\DatamapHook;
use SFC\Staticfilecache\Service\ConfigurationService;
use TYPO3\CMS\Core\Cache\Backend\NullBackend;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Configuration implements SingletonInterface
{
    public const EXTENSION_KEY = 'staticfilecache';

    protected ConfigurationService $configurationService;

    /**
     * Configuration constructor.
     *
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public function __construct()
    {
        $this->configurationService = GeneralUtility::makeInstance(ConfigurationService::class);
    }

    /**
     * Call in ext_localconf.php.
     */
    public function extLocalconf(): void
    {
        $this->registerHooks()
            ->registerCachingFramework()
            ->adjustSystemSettings()
        ;
    }

    /**
     * Register hooks.
     */
    protected function registerHooks(): self
    {
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = DatamapHook::class;

        return $this;
    }

    /**
     * Register caching framework.
     */
    protected function registerCachingFramework(): self
    {
        $useNullBackend = $this->configurationService->isBool('disableInDevelopment') && Environment::getContext()->isDevelopment();

        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][self::EXTENSION_KEY] = [
            'frontend' => UriFrontend::class,
            'backend' => $useNullBackend ? NullBackend::class : StaticFileBackend::class,
            'groups' => [
                'pages',
                'all',
            ],
        ];

        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['remote_file'] = [
            'frontend' => UriFrontend::class,
            'backend' => RemoteFileBackend::class,
            'groups' => [
                'all',
            ],
            'options' => [
                // 'defaultLifetime' => 3600,
                // 'hashLength' => 10,
            ],
        ];

        return $this;
    }

    protected function adjustSystemSettings(): self
    {
        // aim for cacheable frontend responses when using TYPO3's `Content-Security-Policy` behavior
        $GLOBALS['TYPO3_CONF_VARS']['FE']['contentSecurityPolicy']['preferCacheableResponse'] = true;

        return $this;
    }
}

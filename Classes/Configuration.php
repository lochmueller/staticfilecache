<?php

declare(strict_types=1);

namespace SFC\Staticfilecache;

use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use SFC\Staticfilecache\Cache\RemoteFileBackend;
use SFC\Staticfilecache\Cache\Rule\Enable;
use SFC\Staticfilecache\Cache\Rule\NoFakeFrontend;
use SFC\Staticfilecache\Cache\Rule\NoIntScripts;
use SFC\Staticfilecache\Cache\Rule\NoLongPathSegment;
use SFC\Staticfilecache\Cache\Rule\NoNoCache;
use SFC\Staticfilecache\Cache\Rule\NoUserOrGroupSet;
use SFC\Staticfilecache\Cache\Rule\NoWorkspacePreview;
use SFC\Staticfilecache\Cache\Rule\SiteCacheable;
use SFC\Staticfilecache\Cache\Rule\ValidDoktype;
use SFC\Staticfilecache\Cache\Rule\ValidPageInformation;
use SFC\Staticfilecache\Cache\StaticFileBackend;
use SFC\Staticfilecache\Cache\UriFrontend;
use SFC\Staticfilecache\Controller\BackendController;
use SFC\Staticfilecache\Generator\BrotliGenerator;
use SFC\Staticfilecache\Generator\ConfigGenerator;
use SFC\Staticfilecache\Generator\GzipGenerator;
use SFC\Staticfilecache\Generator\HtaccessGenerator;
use SFC\Staticfilecache\Generator\PhpGenerator;
use SFC\Staticfilecache\Generator\PlainGenerator;
use SFC\Staticfilecache\Hook\DatamapHook;
use SFC\Staticfilecache\Service\ConfigurationService;
use SFC\Staticfilecache\Service\HttpPush\FontHttpPush;
use SFC\Staticfilecache\Service\HttpPush\ImageHttpPush;
use SFC\Staticfilecache\Service\HttpPush\ScriptHttpPush;
use SFC\Staticfilecache\Service\HttpPush\StyleHttpPush;
use SFC\Staticfilecache\Service\HttpPush\SvgHttpPush;
use SFC\Staticfilecache\Service\ObjectFactoryService;
use TYPO3\CMS\Core\Cache\Backend\NullBackend;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

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
            ->registerRules()
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
     * Register rules.
     */
    protected function registerRules(): self
    {
        GeneralUtility::makeInstance(ObjectFactoryService::class)->set('CacheRule', [
            'siteCacheable' => SiteCacheable::class,
            'validDoktype' => ValidDoktype::class,
            'noWorkspacePreview' => NoWorkspacePreview::class,
            'noUserOrGroupSet' => NoUserOrGroupSet::class,
            'noIntScripts' => NoIntScripts::class,
            'noNoCache' => NoNoCache::class,
            'enable' => Enable::class,
            'validPageInformation' => ValidPageInformation::class,
            'noFakeFrontend' => NoFakeFrontend::class,
            'noLongPathSegment' => NoLongPathSegment::class,
        ]);

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

<?php

/**
 * Configuration.
 */

declare(strict_types=1);

namespace SFC\Staticfilecache;

use SFC\Staticfilecache\Cache\RemoteFileBackend;
use SFC\Staticfilecache\Cache\Rule\Enable;
use SFC\Staticfilecache\Cache\Rule\LoginDeniedConfiguration;
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
use SFC\Staticfilecache\Generator\ManifestGenerator;
use SFC\Staticfilecache\Generator\PlainGenerator;
use SFC\Staticfilecache\Hook\DatamapHook;
use SFC\Staticfilecache\Hook\LogoffFrontendUser;
use SFC\Staticfilecache\Service\HttpPush\FontHttpPush;
use SFC\Staticfilecache\Service\HttpPush\ImageHttpPush;
use SFC\Staticfilecache\Service\HttpPush\ScriptHttpPush;
use SFC\Staticfilecache\Service\HttpPush\StyleHttpPush;
use SFC\Staticfilecache\Service\ObjectFactoryService;
use TYPO3\CMS\Core\Cache\Backend\NullBackend;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Imaging\IconProvider\FontawesomeIconProvider;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

/**
 * Configuration.
 */
class Configuration extends StaticFileCacheObject
{
    public const EXTENSION_KEY = 'staticfilecache';

    protected array $configuration = [];

    /**
     * Configuration constructor.
     *
     * @throws \TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException
     * @throws \TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException
     */
    public function __construct()
    {
        $this->configuration = (array) GeneralUtility::makeInstance(ExtensionConfiguration::class)->get(self::EXTENSION_KEY);
    }

    /**
     * Call in ext_localconf.php.
     */
    public function extLocalconf(): void
    {
        $this->registerHooks()
            ->registerRules()
            ->registerCachingFramework()
            ->registerIcons()
            ->registerFluidNamespace()
            ->registerGenerators()
            ->registerHttpPushServices()
        ;
    }

    /**
     * Call in ext_tables.php.
     */
    public function extTables(): void
    {
        $this->registerBackendModule();
    }

    /**
     * Add Web>Info module:.
     */
    protected function registerBackendModule(): self
    {
        ExtensionUtility::registerModule(
            'Staticfilecache',
            'web',
            self::EXTENSION_KEY,
            '',
            [
                BackendController::class => 'list,boost,support',
            ],
            [
                'access' => 'user,group',
                'icon' => 'EXT:staticfilecache/Resources/Public/Icons/Extension.svg',
                'labels' => 'LLL:EXT:staticfilecache/Resources/Private/Language/locallang_mod.xlf',
            ]
        );

        return $this;
    }

    /**
     * Register hooks.
     */
    protected function registerHooks(): self
    {
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['logoff_post_processing'][self::EXTENSION_KEY] = LogoffFrontendUser::class.'->logoff';
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
            'loginDeniedConfiguration' => LoginDeniedConfiguration::class,
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
        $useNullBackend = isset($this->configuration['disableInDevelopment']) && $this->configuration['disableInDevelopment'] && Environment::getContext()->isDevelopment();

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

    /**
     * Add fluid namespaces.
     */
    protected function registerFluidNamespace(): self
    {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['sfc'] = ['SFC\\Staticfilecache\\ViewHelpers'];

        return $this;
    }

    /**
     * Register generator.
     */
    protected function registerGenerators(): self
    {
        $generator = [
            'config' => ConfigGenerator::class,
            'htaccess' => HtaccessGenerator::class,
        ];

        if ($this->configuration['enableGeneratorManifest']) {
            $generator['manifest'] = ManifestGenerator::class;
        }
        if ($this->configuration['enableGeneratorPlain']) {
            $generator['plain'] = PlainGenerator::class;
        }
        if ($this->configuration['enableGeneratorGzip']) {
            $generator['gzip'] = GzipGenerator::class;
        }
        if ($this->configuration['enableGeneratorBrotli']) {
            $generator['brotli'] = BrotliGenerator::class;
        }

        GeneralUtility::makeInstance(ObjectFactoryService::class)->set('Generator', $generator);

        return $this;
    }

    /**
     * Register HTTP push services.
     */
    protected function registerHttpPushServices(): self
    {
        GeneralUtility::makeInstance(ObjectFactoryService::class)->set('HttpPush', [
            'style' => StyleHttpPush::class,
            'script' => ScriptHttpPush::class,
            'image' => ImageHttpPush::class,
            'font' => FontHttpPush::class,
        ]);

        return $this;
    }

    /**
     * Register icons.
     */
    protected function registerIcons(): self
    {
        $iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);
        $iconRegistry->registerIcon(
            'brand-amazon',
            FontawesomeIconProvider::class,
            ['name' => 'amazon']
        );
        $iconRegistry->registerIcon(
            'brand-paypal',
            FontawesomeIconProvider::class,
            ['name' => 'paypal']
        );
        $iconRegistry->registerIcon(
            'documentation-book',
            FontawesomeIconProvider::class,
            ['name' => 'book']
        );

        return $this;
    }
}

<?php

/**
 * Configuration.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache;

use SFC\Staticfilecache\Cache\Compression\GzipCompression;
use SFC\Staticfilecache\Cache\RemoteFileBackend;
use SFC\Staticfilecache\Cache\Rule\DomainCacheable;
use SFC\Staticfilecache\Cache\Rule\Enable;
use SFC\Staticfilecache\Cache\Rule\ForceStaticCache;
use SFC\Staticfilecache\Cache\Rule\LoginDeniedConfiguration;
use SFC\Staticfilecache\Cache\Rule\NoBackendUser;
use SFC\Staticfilecache\Cache\Rule\NoFakeFrontend;
use SFC\Staticfilecache\Cache\Rule\NoIntScripts;
use SFC\Staticfilecache\Cache\Rule\NoNoCache;
use SFC\Staticfilecache\Cache\Rule\NoUserOrGroupSet;
use SFC\Staticfilecache\Cache\Rule\NoWorkspacePreview;
use SFC\Staticfilecache\Cache\Rule\PageCacheable;
use SFC\Staticfilecache\Cache\Rule\StaticCacheable;
use SFC\Staticfilecache\Cache\Rule\ValidDoktype;
use SFC\Staticfilecache\Cache\Rule\ValidRequestMethod;
use SFC\Staticfilecache\Cache\Rule\ValidUri;
use SFC\Staticfilecache\Cache\StaticFileBackend;
use SFC\Staticfilecache\Cache\UriFrontend;
use SFC\Staticfilecache\Hook\Cache\ContentPostProcOutput;
use SFC\Staticfilecache\Hook\Cache\Eofe;
use SFC\Staticfilecache\Hook\Cache\InsertPageIncacheHook;
use SFC\Staticfilecache\Hook\Crawler;
use SFC\Staticfilecache\Hook\InitFrontendUser;
use SFC\Staticfilecache\Hook\LogNoCache;
use SFC\Staticfilecache\Hook\LogoffFrontendUser;
use SFC\Staticfilecache\Hook\UninstallProcess;
use TYPO3\CMS\Core\Cache\Backend\NullBackend;
use TYPO3\CMS\Core\Imaging\IconProvider\FontawesomeIconProvider;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use TYPO3\CMS\Extensionmanager\Utility\InstallUtility;

/**
 * Configuration.
 */
class Configuration
{
    /**
     * Add Web>Info module:.
     */
    public static function registerBackendModule()
    {
        ExtensionUtility::registerModule(
            'SFC.Staticfilecache',
            'web',
            'staticfilecache',
            '',
            [
                'Backend' => 'list,removeExpiredPages',
            ],
            [
                'access' => 'user,group',
                'icon' => 'EXT:staticfilecache/Resources/Public/Icons/Extension.png',
                'labels' => 'LLL:EXT:staticfilecache/Resources/Private/Language/locallang_mod.xlf',
            ]
        );
    }

    /**
     * Register hooks.
     */
    public static function registerHooks()
    {
        $configuration = self::getConfiguration();

        // Register with "crawler" extension:
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['crawler']['procInstructions']['tx_staticfilecache_clearstaticfile'] = 'clear static cache file';

        // Hook to process clearing static cached files if "crawler" extension is active:
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['headerNoCache']['staticfilecache'] = Crawler::class . '->clearStaticFile';

        // Log a cache miss if no_cache is true
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-all']['staticfilecache'] = LogNoCache::class . '->log';

        // Add the right cache hook
        $saveCacheHook = $configuration['saveCacheHook'] ?? '';
        switch ($saveCacheHook) {
            case 'ContentPostProcOutput':
                $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-output']['staticfilecache'] = ContentPostProcOutput::class . '->insert';
                break;
            case 'Eofe':
                $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['hook_eofe']['staticfilecache'] = Eofe::class . '->insert';
                break;
            default:
                $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['insertPageIncache']['staticfilecache'] = InsertPageIncacheHook::class;
                break;
        }

        // Set cookie when User logs in
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['initFEuser']['staticfilecache'] = InitFrontendUser::class . '->setFeUserCookie';
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['logoff_post_processing']['staticfilecache'] = LogoffFrontendUser::class . '->logoff';
    }

    /**
     * Register slots.
     */
    public static function registerSlots()
    {
        $ruleClasses = [
            StaticCacheable::class,
            ValidUri::class,
            ValidDoktype::class,
            NoWorkspacePreview::class,
            NoUserOrGroupSet::class,
            NoIntScripts::class,
            LoginDeniedConfiguration::class,
            PageCacheable::class,
            DomainCacheable::class,
            NoNoCache::class,
            NoBackendUser::class,
            Enable::class,
            ValidRequestMethod::class,
            ForceStaticCache::class,
            NoFakeFrontend::class,
        ];

        /** @var Dispatcher $signalSlotDispatcher */
        $signalSlotDispatcher = GeneralUtility::makeInstance(Dispatcher::class);
        foreach ($ruleClasses as $class) {
            $signalSlotDispatcher->connect(StaticFileCache::class, 'cacheRule', $class, 'check');
        }

        $signalSlotDispatcher->connect(StaticFileBackend::class, 'compress', GzipCompression::class, 'compress');

        $signalSlotDispatcher->connect(InstallUtility::class, 'afterExtensionUninstall', UninstallProcess::class, 'afterExtensionUninstall');
    }

    /**
     * Register caching framework.
     */
    public static function registerCachingFramework()
    {
        $configuration = self::getConfiguration();
        $useNullBackend = isset($configuration['disableInDevelopment']) && $configuration['disableInDevelopment'] && GeneralUtility::getApplicationContext()->isDevelopment();

        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['staticfilecache'] = [
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
                'defaultLifetime' => 3600,
            ],
        ];
    }

    /**
     * Register icons.
     */
    public static function registerIcons()
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
        $iconRegistry->registerIcon(
            'brand-patreon',
            SvgIconProvider::class,
            [
                'source' => 'EXT:staticfilecache/Resources/Public/Icons/Patreon.svg',
            ]
        );
    }

    /**
     * Get the current extension configuration.
     *
     * @return array
     */
    public static function getConfiguration(): array
    {
        if (!isset($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['staticfilecache']) || !\is_string($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['staticfilecache'])) {
            return [];
        }

        return (array)\unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['staticfilecache']);
    }
}

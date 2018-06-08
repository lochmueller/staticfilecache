<?php

declare(strict_types=1);
/**
 * Configuration.
 */
namespace SFC\Staticfilecache;

use SFC\Staticfilecache\Cache\Rule\NoIntScripts;
use SFC\Staticfilecache\Cache\Rule\NoUserOrGroupSet;
use SFC\Staticfilecache\Cache\Rule\NoWorkspacePreview;
use SFC\Staticfilecache\Cache\Rule\ValidDoktype;
use SFC\Staticfilecache\Cache\Rule\ValidUri;
use SFC\Staticfilecache\Hook\Cache\ContentPostProcOutput;
use SFC\Staticfilecache\Hook\Cache\Eofe;
use SFC\Staticfilecache\Hook\Cache\InsertPageIncache;
use SFC\Staticfilecache\Hook\Crawler;
use SFC\Staticfilecache\Hook\InitFrontendUser;
use SFC\Staticfilecache\Hook\LogNoCache;
use SFC\Staticfilecache\Hook\LogoffFrontendUser;
use SFC\Staticfilecache\Module\CacheModule;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

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
        ExtensionManagementUtility::insertModuleFunction(
            'web_info',
            CacheModule::class,
            null,
            'LLL:EXT:staticfilecache/Resources/Private/Language/locallang.xlf:module.title'
        );
    }

    /**
     * Register hooks.
     */
    public static function registerHooks()
    {
        $configuration = \unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['staticfilecache']);

        // Register with "crawler" extension:
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['crawler']['procInstructions']['tx_staticfilecache_clearstaticfile'] = 'clear static cache file';

        // Hook to process clearing static cached files if "crawler" extension is active:
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['headerNoCache']['staticfilecache'] = Crawler::class . '->clearStaticFile';

        // Log a cache miss if no_cache is true
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-all']['staticfilecache'] = LogNoCache::class . '->log';

        // Add the right cache hook
        switch ($configuration['saveCacheHook']) {
            case 'ContentPostProcOutput':
                $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-output']['staticfilecache'] = ContentPostProcOutput::class . '->insert';
                break;
            case 'Eofe':
                $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['hook_eofe']['staticfilecache'] = Eofe::class . '->insert';
                break;
            default:
                $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['insertPageIncache']['staticfilecache'] = InsertPageIncache::class;
                break;
        }

        // Set cookie when User logs in
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['initFEuser']['staticfilecache'] = InitFrontendUser::class . '->setFeUserCookie';
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['logoff_post_processing']['staticfilecache'] = LogoffFrontendUser::class . '->logoff';
    }

    public static function registerCommandController()
    {
        // register command controller
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = \SFC\Staticfilecache\Command\CacheCommandController::class;
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = \SFC\Staticfilecache\Command\PublishCommandController::class;
    }

    /**
     * Register slots.
     */
    public static function registerSlots()
    {
        $ruleClasses = [
            // Ensure functionality until https://forge.typo3.org/issues/83212 is fixed
            // \SFC\Staticfilecache\Cache\Rule\StaticCacheable::class,
            ValidUri::class,
            ValidDoktype::class,
            NoWorkspacePreview::class,
            NoUserOrGroupSet::class,
            NoIntScripts::class,
            \SFC\Staticfilecache\Cache\Rule\LoginDeniedConfiguration::class,
            \SFC\Staticfilecache\Cache\Rule\PageCacheable::class,
            \SFC\Staticfilecache\Cache\Rule\NoNoCache::class,
            \SFC\Staticfilecache\Cache\Rule\NoBackendUser::class,
            \SFC\Staticfilecache\Cache\Rule\Enable::class,
            \SFC\Staticfilecache\Cache\Rule\ValidRequestMethod::class,
            \SFC\Staticfilecache\Cache\Rule\ForceStaticCache::class,
            \SFC\Staticfilecache\Cache\Rule\NoFakeFrontend::class,
        ];

        /** @var Dispatcher $signalSlotDispatcher */
        $signalSlotDispatcher = GeneralUtility::makeInstance(Dispatcher::class);
        foreach ($ruleClasses as $class) {
            $signalSlotDispatcher->connect(StaticFileCache::class, 'cacheRule', $class, 'check');
        }
    }

    /**
     * Register caching framework.
     */
    public static function registerCachingFramework()
    {
        $configuration = \unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['staticfilecache']);

        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['staticfilecache'] = [
            'frontend' => \SFC\Staticfilecache\Cache\UriFrontend::class,
            'backend' => \SFC\Staticfilecache\Cache\StaticFileBackend::class,
            'groups' => [
                'pages',
                'all',
            ],
        ];

        // Disable staticfilecache in development if extension configuration 'disableInDevelopment' is set
        if ($configuration['disableInDevelopment'] && GeneralUtility::getApplicationContext()->isDevelopment()) {
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['staticfilecache']['backend'] = \TYPO3\CMS\Core\Cache\Backend\NullBackend::class;
        }
    }

    public static function registerIcons()
    {
        $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
        $iconRegistry->registerIcon(
            'brand-amazon',
            \TYPO3\CMS\Core\Imaging\IconProvider\FontawesomeIconProvider::class,
            ['name' => 'amazon']
        );
        $iconRegistry->registerIcon(
            'brand-paypal',
            \TYPO3\CMS\Core\Imaging\IconProvider\FontawesomeIconProvider::class,
            ['name' => 'paypal']
        );
        $iconRegistry->registerIcon(
            'documentation-book',
            \TYPO3\CMS\Core\Imaging\IconProvider\FontawesomeIconProvider::class,
            ['name' => 'book']
        );
        $iconRegistry->registerIcon(
            'brand-patreon',
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            [
                'source' => 'EXT:staticfilecache/Resources/Public/Icons/Patreon.svg',
            ]
        );
    }
}

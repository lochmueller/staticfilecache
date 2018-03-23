<?php

// declare(strict_types=1);

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

$_EXTCONF = unserialize($_EXTCONF);

// Register with "crawler" extension:
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['crawler']['procInstructions']['tx_staticfilecache_clearstaticfile'] = 'clear static cache file';

// Hook to process clearing static cached files if "crawler" extension is active:
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['headerNoCache'][$_EXTKEY] = \SFC\Staticfilecache\Hook\Crawler::class . '->clearStaticFile';

// Log a cache miss if no_cache is true
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-all'][$_EXTKEY] = \SFC\Staticfilecache\Hook\LogNoCache::class . '->log';

// Create cache (Note: "cache" has to be in lower case)
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['insertPageIncache'][$_EXTKEY] = \SFC\Staticfilecache\StaticFileCache::class;

// Set cookie when User logs in
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['initFEuser'][$_EXTKEY] = \SFC\Staticfilecache\Hook\InitFrontendUser::class . '->setFeUserCookie';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['logoff_post_processing'][$_EXTKEY] = \SFC\Staticfilecache\Hook\LogoffFrontendUser::class . '->logoff';

// register command controller
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = \SFC\Staticfilecache\Command\CacheCommandController::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = \SFC\Staticfilecache\Command\PublishCommandController::class;

$ruleClasses = [
// Ensure functionality until https://forge.typo3.org/issues/83212 is fixed
// \SFC\Staticfilecache\Cache\Rule\StaticCacheable::class,
    \SFC\Staticfilecache\Cache\Rule\ValidUri::class,
    \SFC\Staticfilecache\Cache\Rule\ValidDoktype::class,
    \SFC\Staticfilecache\Cache\Rule\NoWorkspacePreview::class,
    \SFC\Staticfilecache\Cache\Rule\NoUserOrGroupSet::class,
    \SFC\Staticfilecache\Cache\Rule\NoIntScripts::class,
    \SFC\Staticfilecache\Cache\Rule\LoginDeniedConfiguration::class,
    \SFC\Staticfilecache\Cache\Rule\PageCacheable::class,
    \SFC\Staticfilecache\Cache\Rule\NoNoCache::class,
    \SFC\Staticfilecache\Cache\Rule\NoBackendUser::class,
    \SFC\Staticfilecache\Cache\Rule\Enable::class,
    \SFC\Staticfilecache\Cache\Rule\ValidRequestMethod::class,
    \SFC\Staticfilecache\Cache\Rule\ForceStaticCache::class,
    \SFC\Staticfilecache\Cache\Rule\NoFakeFrontend::class
];

/** @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher */
$signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);
foreach ($ruleClasses as $class) {
    $signalSlotDispatcher->connect(\SFC\Staticfilecache\StaticFileCache::class, 'cacheRule', $class, 'check');
}

$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['staticfilecache'] = [
    'frontend' => \SFC\Staticfilecache\Cache\UriFrontend::class,
    'backend'  => \SFC\Staticfilecache\Cache\StaticFileBackend::class,
    'groups'   => [
        'pages',
        'all'
    ]
];

// Disable staticfilecache in development if extension configuration 'disableInDevelopment' is set
if ($_EXTCONF['disableInDevelopment'] && \TYPO3\CMS\Core\Utility\GeneralUtility::getApplicationContext()->isDevelopment()) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['staticfilecache']['backend'] = \TYPO3\CMS\Core\Cache\Backend\NullBackend::class;
}

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

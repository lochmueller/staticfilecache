<?php

/**
 * Extension configuration
 */

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

// Register with "crawler" extension:
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['crawler']['procInstructions']['tx_ncstaticfilecache_clearstaticfile'] = 'clear static cache file';

// Hook to process clearing static cached files if "crawler" extension is active:
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['headerNoCache'][$_EXTKEY] = \SFC\NcStaticfilecache\Hook\Crawler::class . '->clearStaticFile';

// Log a cache miss if no_cache is true
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-all'][$_EXTKEY] = \SFC\NcStaticfilecache\Hook\LogNoCache::class . '->log';

// Create cache
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['insertPageIncache'][$_EXTKEY] = \SFC\NcStaticfilecache\StaticFileCache::class;

// Set cookie when User logs in
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['initFEuser'][$_EXTKEY] = \SFC\NcStaticfilecache\Hook\InitFrontendUser::class . '->setFeUserCookie';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['logoff_post_processing'][$_EXTKEY] = \SFC\NcStaticfilecache\Hook\LogoffFrontendUser::class . '->logoff';

// register command controller
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = \SFC\NcStaticfilecache\Command\CacheCommandController::class;

$ruleClasses = [
    \SFC\NcStaticfilecache\Cache\Rule\StaticCacheable::class,
    \SFC\NcStaticfilecache\Cache\Rule\ValidUri::class,
    \SFC\NcStaticfilecache\Cache\Rule\ValidDoktype::class,
    \SFC\NcStaticfilecache\Cache\Rule\NoWorkspacePreview::class,
    \SFC\NcStaticfilecache\Cache\Rule\NoUserOrGroupSet::class,
    \SFC\NcStaticfilecache\Cache\Rule\NoIntScripts::class,
    \SFC\NcStaticfilecache\Cache\Rule\LoginDeniedConfiguration::class,
    \SFC\NcStaticfilecache\Cache\Rule\PageCacheable::class,
    \SFC\NcStaticfilecache\Cache\Rule\NoNoCache::class,
    \SFC\NcStaticfilecache\Cache\Rule\Enable::class,
];

/** @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher */
$signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);
foreach ($ruleClasses as $class) {
    $signalSlotDispatcher->connect(\SFC\NcStaticfilecache\StaticFileCache::class, 'cacheRule', $class, 'check');
}

// new Cache for Static file caches
$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['static_file_cache'] = [
    'frontend' => \SFC\NcStaticfilecache\Cache\UriFrontend::class,
    'backend' => \SFC\NcStaticfilecache\Cache\StaticFileBackend::class,
    'groups' => [
        'pages',
        'all'
    ],
];

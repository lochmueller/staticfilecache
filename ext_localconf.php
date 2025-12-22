<?php

declare(strict_types=1);

use SFC\Staticfilecache\Cache\RemoteFileBackend;
use SFC\Staticfilecache\Cache\StaticFileBackend;
use SFC\Staticfilecache\Cache\UriFrontend;
use SFC\Staticfilecache\Hook\DatamapHook;
use TYPO3\CMS\Core\Cache\Backend\NullBackend;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

defined('TYPO3') || die();

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = DatamapHook::class;

$extensionConfig = (array)GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('staticfilecache');
$useNullBackend = $extensionConfig['disableInDevelopment'] && Environment::getContext()->isDevelopment();



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
        // 'defaultLifetime' => 3600,
        // 'hashLength' => 10,
    ],
];

// aim for cacheable frontend responses when using TYPO3's `Content-Security-Policy` behavior
$GLOBALS['TYPO3_CONF_VARS']['FE']['contentSecurityPolicy']['preferCacheableResponse'] = true;

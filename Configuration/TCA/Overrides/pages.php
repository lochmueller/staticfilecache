<?php

declare(strict_types=1);

$tmp = [
    'tx_staticfilecache_cache' => [
        'exclude' => 0,
        'label' => 'LLL:EXT:staticfilecache/Resources/Private/Language/locallang.xlf:staticfilecache.tx_staticfilecache_cache',
        'config' => [
            'type' => 'check',
            'default' => '1',
        ],
    ],
    'tx_staticfilecache_cache_force' => [
        'exclude' => 0,
        'label' => 'LLL:EXT:staticfilecache/Resources/Private/Language/locallang.xlf:staticfilecache.tx_staticfilecache_cache_force',
        'config' => [
            'type' => 'check',
            'default' => '0',
        ],
    ],
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('pages', $tmp);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette(
    'pages',
    'caching',
    '--linebreak--,tx_staticfilecache_cache,tx_staticfilecache_cache_force'
);

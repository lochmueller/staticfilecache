<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

$tmp = [
    'tx_staticfilecache_cache' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:staticfilecache/Resources/Private/Language/locallang.xlf:staticfilecache.tx_staticfilecache_cache',
        'config' => [
            'type' => 'check',
            'default' => '1',
        ],
    ],
    'tx_staticfilecache_cache_force' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:staticfilecache/Resources/Private/Language/locallang.xlf:staticfilecache.tx_staticfilecache_cache_force',
        'description' => 'LLL:EXT:staticfilecache/Resources/Private/Language/locallang.xlf:staticfilecache.tx_staticfilecache_cache_force.desc',
        'config' => [
            'type' => 'check',
            'default' => '0',
        ],
    ],
    'tx_staticfilecache_cache_offline' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:staticfilecache/Resources/Private/Language/locallang.xlf:staticfilecache.tx_staticfilecache_cache_offline',
        'config' => [
            'type' => 'check',
            'default' => '0',
        ],
    ],
    'tx_staticfilecache_cache_priority' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:staticfilecache/Resources/Private/Language/locallang.xlf:staticfilecache.tx_staticfilecache_cache_priority',
        'config' => [
            'type' => 'input',
            'default' => '0',
            'eval' => 'int+',
        ],
    ],
];

ExtensionManagementUtility::addTCAcolumns('pages', $tmp);

ExtensionManagementUtility::addFieldsToPalette(
    'pages',
    'caching',
    '--linebreak--,'.implode(',', array_keys($tmp))
);

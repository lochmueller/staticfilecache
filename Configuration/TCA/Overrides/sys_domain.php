<?php

declare(strict_types = 1);

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
];

ExtensionManagementUtility::addTCAcolumns('sys_domain', $tmp);

ExtensionManagementUtility::addToAllTCAtypes('sys_domain', 'tx_staticfilecache_cache');

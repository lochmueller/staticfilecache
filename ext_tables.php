<?php declare(strict_types=1);
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

if (TYPO3_MODE == 'BE') {
    // Add Web>Info module:
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::insertModuleFunction(
        'web_info',
        \SFC\Staticfilecache\Module\CacheModule::class,
        null,
        'LLL:EXT:staticfilecache/Resources/Private/Language/locallang.xlf:module.title'
    );
}

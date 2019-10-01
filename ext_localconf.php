<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\SFC\Staticfilecache\Configuration::class)
    ->registerHooks()
    ->registerSlots()
    ->registerCachingFramework()
    ->registerIcons()
    ->registerFluidNamespace()
    ->registerEid();

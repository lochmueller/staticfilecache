<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

\SFC\Staticfilecache\Configuration::registerHooks();
\SFC\Staticfilecache\Configuration::registerCommandController();
\SFC\Staticfilecache\Configuration::registerSlots();
\SFC\Staticfilecache\Configuration::registerCachingFramework();
\SFC\Staticfilecache\Configuration::registerIcons();

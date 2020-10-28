<?php

declare(strict_types=1);

$GLOBALS['SiteConfiguration']['site']['columns']['disableStaticFileCache'] = [
    'label' => 'Disable StaticFileCache',
    'description' => 'Note: If the static file cache of a site was enabled (default!) and you disable the cache, please take care to drop the static file cache manually or run `typo3cms staticfilecache:flushCache --force-boost-mode-flush` once!',
    'config' => [
        'type' => 'check',
    ],
];

$GLOBALS['SiteConfiguration']['site']['types']['0']['showitem'] = str_replace(
    'base,',
    'base, disableStaticFileCache, ',
    $GLOBALS['SiteConfiguration']['site']['types']['0']['showitem']
);

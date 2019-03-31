<?php

$GLOBALS['SiteConfiguration']['site']['columns']['disableStaticFileCache'] = [
    'label' => 'Disable StaticFileCache',
    'config' => [
        'type' => 'check',
    ],
];

$GLOBALS['SiteConfiguration']['site']['types']['0']['showitem'] = str_replace(
    'base,',
    'base, disableStaticFileCache, ',
    $GLOBALS['SiteConfiguration']['site']['types']['0']['showitem']
);

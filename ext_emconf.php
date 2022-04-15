<?php

declare(strict_types=1);

$EM_CONF[$_EXTKEY] = [
    'title' => 'StaticFileCache',
    'description' => 'Transparent static file cache solution using mod_rewrite and mod_expires. Increase performance for static pages by a factor of 230!!',
    'version' => '12.4.5',
    'category' => 'fe',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.21-11.5.99',
            'php' => '7.4.0-8.0.99',
        ],
    ],
    'state' => 'stable',
    'clearCacheOnLoad' => true,
    'author' => 'StaticFileCache Team',
    'author_email' => 'tim@fruit-lab.de',
    'author_company' => 'StaticFileCache Team',
    'autoload' => [
        'psr-4' => [
            'SFC\\Staticfilecache\\' => 'Classes'
        ],
    ],
];

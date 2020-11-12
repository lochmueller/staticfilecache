<?php

declare(strict_types=1);

$EM_CONF[$_EXTKEY] = [
    'title' => 'StaticFileCache',
    'description' => 'Transparent StaticFileCache solution using mod_rewrite and mod_expires. Increase performance for static pages by a factor of 230!!',
    'version' => '12.0.4',
    'category' => 'fe',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.6-10.4.99',
            'php' => '7.3.0-7.4.99',
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

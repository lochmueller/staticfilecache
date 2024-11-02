<?php

/** @var string $_EXTKEY */
$EM_CONF[$_EXTKEY] = [
    'title' => 'StaticFileCache',
    'description' => 'Transparent static file cache solution using mod_rewrite and mod_expires. Increase performance for static pages by a factor of 230!!',
    'version' => '15.0.1',
    'category' => 'fe',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-13.4.99',
            'backend' => '12.4.0-13.4.99',
            'php' => '8.1.0-8.99.99',
        ],
    ],
    'state' => 'stable',
    'author' => 'StaticFileCache Team',
    'author_email' => 'tim@fruit-lab.de',
    'author_company' => 'StaticFileCache Team',
    'autoload' => [
        'psr-4' => [
            'SFC\\Staticfilecache\\' => 'Classes',
        ],
    ],
];

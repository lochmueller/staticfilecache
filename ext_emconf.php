<?php

declare(strict_types=1);

$EM_CONF[$_EXTKEY] = [
    'title' => 'StaticFileCache',
    'description' => 'Transparent StaticFileCache solution using mod_rewrite and mod_expires. Increase performance for static pages by a factor of 230!!',
    'category' => 'fe',
    'version' => '11.0.0',
    'state' => 'stable',
    'modify_tables' => 'pages',
    'clearcacheonload' => true,
    'author' => 'StaticFileCache Team',
    'author_email' => 'tim@fruit-lab.de',
    'author_company' => 'StaticFileCache Team',
    'constraints' => [
        'depends' => [
            'typo3' => '9.5.0-10.4.99',
            'php' => '7.3.0-7.4.99',
        ],
    ],
    'autoload' => [
        'psr-4' => [
            'SFC\\Staticfilecache\\' => 'Classes'
        ],
    ],
];

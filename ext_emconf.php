<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Static File Cache',
    'description' => 'Transparent static file cache solution using mod_rewrite and mod_expires. Increase response times for static pages by a factor of 230!!',
    'category' => 'fe',
    'version' => '3.8.2',
    'state' => 'stable',
    'modify_tables' => 'pages',
    'clearcacheonload' => true,
    'author' => 'Static File Cache Team',
    'author_email' => 'tim@fruit-lab.de',
    'author_company' => 'Static File Cache Team',
    'constraints' => [
        'depends' => [
            'typo3' => '6.2.0-7.99.99',
            'php' => '5.6.0-0.0.0',
        ],
    ],
    'autoload' => [
        'psr-4' => [
            'SFC\\Staticfilecache\\' => 'Classes'
        ],
    ],
];

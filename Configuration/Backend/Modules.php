<?php

declare(strict_types=1);

use SFC\Staticfilecache\Controller\BackendController;

return [
    'web_staticfilecache' => [
        'parent' => 'web',
        'position' => [],
        'access' => 'user,group',
        'workspaces' => '*',
        'path' => '/module/system/staticfilecache',
        'iconIdentifier' => 'module-staticfilecache',
        'labels' => 'LLL:EXT:staticfilecache/Resources/Private/Language/locallang_mod.xlf',
        'extensionName' => 'staticfilecache',
        'controllerActions' => [
            BackendController::class => [
                'list',
                'boost',
                'support',
            ],
        ],
    ],
];

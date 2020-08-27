<?php

use SFC\Staticfilecache\Middleware\FallbackMiddleware;
use SFC\Staticfilecache\Middleware\GenerateMiddleware;
use SFC\Staticfilecache\Middleware\PrepareMiddleware;

return [
    'frontend' => [
        'staticfilecache/prepare' => [
            'target' => PrepareMiddleware::class,
            'before' => [
                'typo3/cms-frontend/base-redirect-resolver',
            ],
            'after' => [
                'typo3/cms-frontend/site',
            ],
        ],
        'staticfilecache/generate' => [
            'target' => GenerateMiddleware::class,
            'before' => [
                'staticfilecache/prepare',
            ],
        ],
        'staticfilecache/fallback' => [
            'target' => FallbackMiddleware::class,
            'before' => [
                'typo3/cms-frontend/timetracker',
            ],
        ],
    ],
];

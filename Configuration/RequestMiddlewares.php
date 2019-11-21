<?php

return [
    'frontend' => [
        'staticfilecache/prepare' => [
            'target' => \SFC\Staticfilecache\Middleware\PrepareMiddleware::class,
            'before' => [
                'typo3/cms-frontend/base-redirect-resolver',
            ],
            'after' => [
                'typo3/cms-frontend/site',
            ],
        ],
        'staticfilecache/generate' => [
            'target' => \SFC\Staticfilecache\Middleware\GenerateMiddleware::class,
            'before' => [
                'staticfilecache/prepare',
            ],
        ],
        'staticfilecache/fallback' => [
            'target' => \SFC\Staticfilecache\Middleware\FallbackMiddleware::class,
            'before' => [
                'typo3/cms-frontend/timetracker',
            ],
        ],
    ],
];

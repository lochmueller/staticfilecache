<?php

return [
    'frontend' => [
        'staticfilecache/prepare' => [
            'target' => \SFC\Staticfilecache\Middleware\PrepareMiddleware::class,
            'before' => [
                'typo3/cms-frontend/tsfe',
            ],
            'after' => [
                'typo3/cms-frontend/eid',
            ],
        ],
        'staticfilecache/generate' => [
            'target' => \SFC\Staticfilecache\Middleware\GenerateMiddleware::class,
            'before' => [
                'staticfilecache/prepare',
            ],
            'after' => [
                'typo3/cms-frontend/eid',
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

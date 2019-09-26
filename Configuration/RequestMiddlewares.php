<?php

return [
    'frontend' => [
        'staticfilecache/generate' => [
            'target' => \SFC\Staticfilecache\Middleware\GenerateMiddleware::class,
            'before' => [
                'typo3/cms-frontend/tsfe',
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

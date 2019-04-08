<?php

return [
    'frontend' => [
        'static-file-cache' => [
            'target' => \SFC\Staticfilecache\Middleware\StaticFileCacheMiddleware::class,
            'before' => [
                'typo3/cms-frontend/tsfe',
            ],
        ]
    ]
];

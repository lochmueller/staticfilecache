<?php

declare(strict_types=1);

use SFC\Staticfilecache\Middleware\CookieCheckMiddleware;
use SFC\Staticfilecache\Middleware\FallbackMiddleware;
use SFC\Staticfilecache\Middleware\FrontendUserMiddleware;
use SFC\Staticfilecache\Middleware\FrontendCacheMiddleware;
use SFC\Staticfilecache\Middleware\GenerateMiddleware;
use SFC\Staticfilecache\Middleware\PrepareMiddleware;

return [
    'frontend' => [
        'staticfilecache/prepare' => [
            'target' => PrepareMiddleware::class,
            'before' => [
                'typo3/cms-core/cache-timeout',
            ],
            'after' => [
                'typo3/cms-frontend/site',
                'typo3/cms-frontend/page-resolver',
                'typo3/cms-frontend/prepare-tsfe-rendering', // We need frontend.page.information
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
        'staticfilecache/frontend-user' => [
            'target' => FrontendUserMiddleware::class,
            'after' => [
                'typo3/cms-frontend/authentication',
            ],
            'before' => [
                'staticfilecache/generate',
            ],
        ],
        'staticfilecache/cookie-check' => [
            'target' => CookieCheckMiddleware::class,
            'before' => [
                'staticfilecache/generate',
            ],
            'after' => [
                'staticfilecache/frontend-user',
            ],
        ],
    ],
];

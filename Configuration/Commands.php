<?php

use SFC\Staticfilecache\Command\BoostQueueCleanupCommand;
use SFC\Staticfilecache\Command\BoostQueueRunCommand;
use SFC\Staticfilecache\Command\FlushCacheCommand;
use SFC\Staticfilecache\Command\PublishCommand;
use SFC\Staticfilecache\Command\RemoveExpiredPagesCommand;

return [
    'staticfilecache:publish' => [
        'class' => PublishCommand::class
    ],
    'staticfilecache:removeExpiredPages' => [
        'class' => RemoveExpiredPagesCommand::class
    ],
    'staticfilecache:boostQueueCleanup' => [
        'class' => BoostQueueCleanupCommand::class
    ],
    'staticfilecache:boostQueueRun' => [
        'class' => BoostQueueRunCommand::class
    ],
    'staticfilecache:flushCache' => [
        'class' => FlushCacheCommand::class
    ],
];

<?php

use SFC\Staticfilecache\Command\BoostQueueCleanupCommand;
use SFC\Staticfilecache\Command\BoostQueueCommand;
use SFC\Staticfilecache\Command\FlushCacheCommand;
use SFC\Staticfilecache\Command\RemoveExpiredPagesCommand;

return [
    'staticfilecache:removeExpiredPages' => [
        'class' => RemoveExpiredPagesCommand::class
    ],
    'staticfilecache:boostQueue' => [
        'class' => BoostQueueCommand::class
    ],
    'staticfilecache:flushCache' => [
        'class' => FlushCacheCommand::class
    ],
];

<?php

use SFC\Staticfilecache\Command\BoostQueueCommand;
use SFC\Staticfilecache\Command\FlushCacheCommand;

return [
    'staticfilecache:boostQueue' => [
        'class' => BoostQueueCommand::class
    ],
    'staticfilecache:flushCache' => [
        'class' => FlushCacheCommand::class
    ],
];

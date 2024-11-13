<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Cache\Listener;

use Psr\Http\Message\ServerRequestInterface;
use SFC\Staticfilecache\Event\CacheRuleEvent;
use SFC\Staticfilecache\Tests\Unit\AbstractTest;

abstract class AbstractListenerTest extends AbstractTest
{

    protected function emptyCacheRuleEvent(): CacheRuleEvent
    {
        return new CacheRuleEvent(
            $this->getMockBuilder(ServerRequestInterface::class)->getMock(),
            [],
            false
        );
    }

}

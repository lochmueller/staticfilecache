<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Cache\Listener;

use SFC\Staticfilecache\Cache\Listener\EnableListener;
use SFC\Staticfilecache\Service\ConfigurationService;

class EnableListenerTest extends AbstractListenerTest
{
    public function testNoExplanation(): void
    {
        $configurationService = $this->getMockBuilder(ConfigurationService::class)->disableOriginalConstructor()->getMock();
        $configurationService->method('isBool')->willReturn(false);

        $listener = new EnableListener($configurationService);

        $event = $this->emptyCacheRuleEvent();
        $listener($event);

        self::assertEquals([], $event->getExplanation());
        self::assertEquals(false, $event->isSkipProcessing());

    }


    public function testAddExplanation(): void
    {
        $configurationService = $this->getMockBuilder(ConfigurationService::class)->disableOriginalConstructor()->getMock();
        $configurationService->method('isBool')->willReturn(true);

        $listener = new EnableListener($configurationService);

        $event = $this->emptyCacheRuleEvent();
        $listener($event);

        self::assertNotEquals([], $event->getExplanation());
        self::assertEquals(false, $event->isSkipProcessing());

    }
}

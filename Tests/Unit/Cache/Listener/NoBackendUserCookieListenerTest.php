<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Cache\Listener;

use SFC\Staticfilecache\Cache\Listener\NoBackendUserCookieListener;

class NoBackendUserCookieListenerTest extends AbstractListenerTest
{
    protected function setUp(): void
    {
        parent::setUp();
        $GLOBALS['TYPO3_CONF_VARS']['BE']['cookieName'] = 'be_typo_user';
        unset($_COOKIE['be_typo_user']);
    }

    protected function tearDown(): void
    {
        unset($_COOKIE['be_typo_user']);
        parent::tearDown();
    }

    public function testNoExplanation(): void
    {
        $listener = new NoBackendUserCookieListener();

        $event = $this->emptyCacheRuleEvent();
        $listener($event);

        self::assertEquals([], $event->getExplanation());
        self::assertEquals(false, $event->isSkipProcessing());
    }

    public function testExplanationAndSkip(): void
    {
        $_COOKIE['be_typo_user'] = 'session';

        $listener = new NoBackendUserCookieListener();

        $event = $this->emptyCacheRuleEvent();
        $listener($event);

        self::assertArrayHasKey(NoBackendUserCookieListener::class, $event->getExplanation());
        self::assertEquals(true, $event->isSkipProcessing());
    }
}

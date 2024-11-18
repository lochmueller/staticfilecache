<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Cache\Listener;

use SFC\Staticfilecache\Cache\Listener\NoBackendUserListener;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\UserAspect;

class NoBackendUserListenerTest extends AbstractListenerTest
{
    public function testNoExplanation(): void
    {
        $userAuth = new BackendUserAuthentication();

        $userAspect = new UserAspect($userAuth);

        $context = new Context();
        $context->setAspect('backend.user', $userAspect);

        $listener = new NoBackendUserListener($context);

        $event = $this->emptyCacheRuleEvent();
        $listener($event);

        self::assertEquals([], $event->getExplanation());
        self::assertEquals(false, $event->isSkipProcessing());
    }

    public function testExplanationAndSkip(): void
    {
        $userAuth = new BackendUserAuthentication();
        $userAuth->user = ['uid' => 5];

        $userAspect = new UserAspect($userAuth);

        $context = new Context();
        $context->setAspect('backend.user', $userAspect);

        $listener = new NoBackendUserListener($context);

        $event = $this->emptyCacheRuleEvent();
        $listener($event);

        self::assertNotEquals([], $event->getExplanation());
        self::assertEquals(true, $event->isSkipProcessing());
    }
}

<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Cache\Listener;

use SFC\Staticfilecache\Cache\Listener\NoUserOrGroupSetListener;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

class NoUserOrGroupSetListenerTest extends AbstractListenerTest
{
    public function testNoExplanation(): void
    {
        $context = new Context();
        $context->setAspect('frontend.user', new UserAspect(new FrontendUserAuthentication()));

        $listener = new NoUserOrGroupSetListener($context);

        $event = $this->emptyCacheRuleEvent();
        $listener($event);

        self::assertEquals([], $event->getExplanation());
        self::assertEquals(false, $event->isSkipProcessing());
    }

    public function testExplanationForLoggedInUser(): void
    {
        $userAuth = new FrontendUserAuthentication();
        $userAuth->user = ['uid' => 5];

        $context = new Context();
        $context->setAspect('frontend.user', new UserAspect($userAuth));

        $listener = new NoUserOrGroupSetListener($context);

        $event = $this->emptyCacheRuleEvent();
        $listener($event);

        self::assertArrayHasKey(NoUserOrGroupSetListener::class, $event->getExplanation());
        self::assertEquals(false, $event->isSkipProcessing());
    }

    public function testExplanationForAlternativeGroups(): void
    {
        $context = new Context();
        $context->setAspect('frontend.user', new UserAspect(new FrontendUserAuthentication(), [0, -1, 3]));

        $listener = new NoUserOrGroupSetListener($context);

        $event = $this->emptyCacheRuleEvent();
        $listener($event);

        self::assertArrayHasKey(NoUserOrGroupSetListener::class, $event->getExplanation());
    }
}

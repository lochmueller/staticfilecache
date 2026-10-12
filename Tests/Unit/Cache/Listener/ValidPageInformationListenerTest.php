<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Cache\Listener;

use SFC\Staticfilecache\Cache\Listener\ValidPageInformationListener;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Frontend\Page\PageInformation;

class ValidPageInformationListenerTest extends AbstractListenerTest
{
    public function testNoExplanation(): void
    {
        $listener = new ValidPageInformationListener();

        $event = $this->cacheRuleEvent((new ServerRequest())->withAttribute('frontend.page.information', new PageInformation()));
        $listener($event);

        self::assertEquals([], $event->getExplanation());
        self::assertEquals(false, $event->isSkipProcessing());
    }

    public function testExplanationAndSkip(): void
    {
        $listener = new ValidPageInformationListener();

        $event = $this->emptyCacheRuleEvent();
        $listener($event);

        self::assertArrayHasKey(ValidPageInformationListener::class, $event->getExplanation());
        self::assertEquals(true, $event->isSkipProcessing());
    }
}

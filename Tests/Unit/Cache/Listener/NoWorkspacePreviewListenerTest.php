<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Cache\Listener;

use SFC\Staticfilecache\Cache\Listener\NoWorkspacePreviewListener;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\WorkspaceAspect;

class NoWorkspacePreviewListenerTest extends AbstractListenerTest
{
    public function testNoExplanation(): void
    {
        $context = new Context();
        $context->setAspect('workspace', new WorkspaceAspect(0));

        $listener = new NoWorkspacePreviewListener($context);

        $event = $this->emptyCacheRuleEvent();
        $listener($event);

        self::assertEquals([], $event->getExplanation());
        self::assertEquals(false, $event->isSkipProcessing());
    }

    public function testExplanationAndSkip(): void
    {
        $context = new Context();
        $context->setAspect('workspace', new WorkspaceAspect(1));

        $listener = new NoWorkspacePreviewListener($context);

        $event = $this->emptyCacheRuleEvent();
        $listener($event);

        self::assertNotEquals([], $event->getExplanation());
    }


}

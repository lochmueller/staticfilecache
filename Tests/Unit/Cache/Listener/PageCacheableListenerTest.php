<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Cache\Listener;

use SFC\Staticfilecache\Cache\Listener\PageCacheableListener;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Frontend\Page\PageInformation;

class PageCacheableListenerTest extends AbstractListenerTest
{
    public function testNoExplanationWithoutPageInformation(): void
    {
        $listener = new PageCacheableListener();

        $event = $this->emptyCacheRuleEvent();
        $listener($event);

        self::assertEquals([], $event->getExplanation());
        self::assertEquals(false, $event->isSkipProcessing());
    }

    public function testNoExplanationWithoutPageField(): void
    {
        $listener = new PageCacheableListener();

        $event = $this->cacheRuleEvent($this->createRequest(['uid' => 1]));
        $listener($event);

        self::assertEquals([], $event->getExplanation());
    }

    public function testNoExplanationForCacheablePage(): void
    {
        $listener = new PageCacheableListener();

        $event = $this->cacheRuleEvent($this->createRequest(['uid' => 1, 'tx_staticfilecache_cache' => 1]));
        $listener($event);

        self::assertEquals([], $event->getExplanation());
    }

    public function testExplanationForDisabledPage(): void
    {
        $listener = new PageCacheableListener();

        $event = $this->cacheRuleEvent($this->createRequest(['uid' => 1, 'tx_staticfilecache_cache' => 0]));
        $listener($event);

        self::assertArrayHasKey(PageCacheableListener::class, $event->getExplanation());
        self::assertEquals(false, $event->isSkipProcessing());
    }

    /**
     * @param array<string, mixed> $pageRecord
     */
    protected function createRequest(array $pageRecord): ServerRequest
    {
        $pageInformation = new PageInformation();
        $pageInformation->setPageRecord($pageRecord);

        return (new ServerRequest())->withAttribute('frontend.page.information', $pageInformation);
    }
}

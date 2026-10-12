<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Cache\Listener;

use SFC\Staticfilecache\Cache\Listener\SiteCacheableListener;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Site\Entity\Site;

class SiteCacheableListenerTest extends AbstractListenerTest
{
    public function testNoExplanationWithoutSite(): void
    {
        $listener = new SiteCacheableListener();

        $event = $this->emptyCacheRuleEvent();
        $listener($event);

        self::assertEquals([], $event->getExplanation());
        self::assertEquals(false, $event->isSkipProcessing());
    }

    public function testNoExplanationForEnabledSite(): void
    {
        $listener = new SiteCacheableListener();

        $event = $this->cacheRuleEvent($this->createRequest(['disableStaticFileCache' => false]));
        $listener($event);

        self::assertEquals([], $event->getExplanation());
    }

    public function testExplanationForDisabledSite(): void
    {
        $listener = new SiteCacheableListener();

        $event = $this->cacheRuleEvent($this->createRequest(['disableStaticFileCache' => true]));
        $listener($event);

        self::assertEquals(
            [SiteCacheableListener::class => 'static cache disabled on site configuration: main'],
            $event->getExplanation()
        );
        self::assertEquals(false, $event->isSkipProcessing());
    }

    protected function createRequest(array $configuration): ServerRequest
    {
        $site = new Site('main', 1, $configuration + ['base' => 'https://example.com/']);

        return (new ServerRequest())->withAttribute('site', $site);
    }
}

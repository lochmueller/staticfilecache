<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Cache\Listener;

use Psr\EventDispatcher\EventDispatcherInterface;
use SFC\Staticfilecache\Cache\Listener\ForceStaticCacheListener;
use SFC\Staticfilecache\Event\ForceStaticFileCacheEvent;
use TYPO3\CMS\Core\EventDispatcher\NoopEventDispatcher;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\TypoScript\AST\Node\RootNode;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3\CMS\Frontend\Page\PageInformation;

class ForceStaticCacheListenerTest extends AbstractListenerTest
{
    public function testNoExplanation(): void
    {
        $listener = new ForceStaticCacheListener(new NoopEventDispatcher());

        $cacheRuleEvent = $this->cacheRuleEvent(new ServerRequest(), null, ['dummy' => 'dummy']);

        $listener($cacheRuleEvent);

        self::assertEquals(['dummy' => 'dummy'], $cacheRuleEvent->getExplanation());
        self::assertEquals(false, $cacheRuleEvent->isSkipProcessing());
    }

    public function testKeepSkipWithoutPageInformation(): void
    {
        $listener = new ForceStaticCacheListener(new NoopEventDispatcher());

        $cacheRuleEvent = $this->cacheRuleEvent(new ServerRequest(), null, ['dummy' => 'dummy'], true);
        $listener($cacheRuleEvent);

        self::assertEquals(['dummy' => 'dummy'], $cacheRuleEvent->getExplanation());
        self::assertEquals(true, $cacheRuleEvent->isSkipProcessing());
    }

    public function testKeepSkipWithoutForceFlag(): void
    {
        $listener = new ForceStaticCacheListener(new NoopEventDispatcher());

        $cacheRuleEvent = $this->cacheRuleEvent($this->createRequest(['tx_staticfilecache_cache_force' => 0]), null, ['dummy' => 'dummy'], true);
        $listener($cacheRuleEvent);

        self::assertEquals(['dummy' => 'dummy'], $cacheRuleEvent->getExplanation());
        self::assertEquals(true, $cacheRuleEvent->isSkipProcessing());
    }

    public function testForceStaticCacheViaPageRecord(): void
    {
        $frontendTypoScript = new FrontendTypoScript(new RootNode(), [], [], []);
        $frontendTypoScript->setConfigArray([]);

        $request = $this->createRequest(['tx_staticfilecache_cache_force' => 1])
            ->withAttribute('frontend.typoscript', $frontendTypoScript);

        $listener = new ForceStaticCacheListener(new NoopEventDispatcher());

        $cacheRuleEvent = $this->cacheRuleEvent($request, null, ['dummy' => 'dummy'], true);
        $listener($cacheRuleEvent);

        self::assertEquals([], $cacheRuleEvent->getExplanation());
        self::assertEquals(false, $cacheRuleEvent->isSkipProcessing());
        self::assertEquals(['INTincScript' => []], $frontendTypoScript->getConfigArray());
    }

    public function testForceStaticCacheViaEvent(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::once())
            ->method('dispatch')
            ->willReturnCallback(static function (ForceStaticFileCacheEvent $event): ForceStaticFileCacheEvent {
                $event->setForceStatic(true);

                return $event;
            });

        $listener = new ForceStaticCacheListener($eventDispatcher);

        $cacheRuleEvent = $this->cacheRuleEvent($this->createRequest([]), null, ['dummy' => 'dummy'], true);
        $listener($cacheRuleEvent);

        self::assertEquals([], $cacheRuleEvent->getExplanation());
        self::assertEquals(false, $cacheRuleEvent->isSkipProcessing());
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

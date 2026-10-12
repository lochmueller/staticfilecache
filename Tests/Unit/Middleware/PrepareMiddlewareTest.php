<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Middleware;

use PHPUnit\Framework\MockObject\MockObject;
use SFC\Staticfilecache\Event\CacheRuleEvent;
use SFC\Staticfilecache\Middleware\PrepareMiddleware;
use SFC\Staticfilecache\Service\HttpPushService;
use SFC\Staticfilecache\Service\InlineAssetsService;
use TYPO3\CMS\Core\Cache\CacheDataCollector;
use TYPO3\CMS\Core\Cache\CacheTag;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Http\ServerRequest;

/**
 * PrepareMiddlewareTest.
 *
 * @internal
 * @coversNothing
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
final class PrepareMiddlewareTest extends AbstractMiddlewareTest
{
    protected HttpPushService&MockObject $httpPushService;

    protected InlineAssetsService&MockObject $inlineAssetsService;

    protected ServerRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        $this->httpPushService = $this->createMock(HttpPushService::class);
        $this->httpPushService->method('getHttpPushHeaders')->willReturn([]);
        $this->inlineAssetsService = $this->createMock(InlineAssetsService::class);
        $this->inlineAssetsService->method('replaceInlineContent')->willReturnArgument(0);

        $collector = new CacheDataCollector();
        $collector->addCacheTags(new CacheTag('pageId_1'), new CacheTag('tt_content'));
        $this->request = (new ServerRequest('https://www.example.com/page/'))->withAttribute('frontend.cache.collector', $collector);
        $GLOBALS['TYPO3_REQUEST'] = $this->request;

        $this->setExtensionConfiguration();
    }

    public function testCachableResponse(): void
    {
        $response = $this->getMiddleware()->process($this->request, $this->getHandler(new HtmlResponse('<html></html>')));

        self::assertSame('1', $response->getHeaderLine('X-SFC-Cachable'));
        self::assertSame(['pageId_1', 'tt_content', 'sfc_domain_www_example_com'], $response->getHeader('X-SFC-Tags'));
        self::assertFalse($response->hasHeader('X-SFC-Explanation'));
        self::assertSame('<html></html>', (string) $response->getBody());
    }

    public function testNoDomainTagWhenClearCacheForAllDomains(): void
    {
        $this->setExtensionConfiguration(['clearCacheForAllDomains' => '1']);

        $response = $this->getMiddleware()->process($this->request, $this->getHandler(new HtmlResponse('')));

        self::assertSame(['pageId_1', 'tt_content'], $response->getHeader('X-SFC-Tags'));
    }

    public function testResponseWithExplanation(): void
    {
        $eventDispatcher = $this->getEventDispatcher(static function (object $event): void {
            if ($event instanceof CacheRuleEvent) {
                $event->addExplanation('first', 'First reason');
                $event->addExplanation('second', 'Second reason');
            }
        });

        $response = $this->getMiddleware($eventDispatcher)->process($this->request, $this->getHandler(new HtmlResponse('')));

        self::assertSame('0', $response->getHeaderLine('X-SFC-Cachable'));
        self::assertSame(['First reason', 'Second reason'], $response->getHeader('X-SFC-Explanation'));
        self::assertContains('explanation', $response->getHeader('X-SFC-Tags'));
    }

    public function testSkipProcessing(): void
    {
        $eventDispatcher = $this->getEventDispatcher(static function (object $event): void {
            if ($event instanceof CacheRuleEvent) {
                $event->setSkipProcessing(true);
            }
        });

        $response = $this->getMiddleware($eventDispatcher)->process($this->request, $this->getHandler(new HtmlResponse('content')));

        self::assertFalse($response->hasHeader('X-SFC-Cachable'));
        self::assertFalse($response->hasHeader('X-SFC-Tags'));
        self::assertSame('content', (string) $response->getBody());
    }

    public function testInlineAssetsAreReplaced(): void
    {
        $inlineAssetsService = $this->createMock(InlineAssetsService::class);
        $inlineAssetsService->expects(self::once())->method('replaceInlineContent')->with('original')->willReturn('replaced');

        $middleware = new PrepareMiddleware($this->getEventDispatcher(), $this->httpPushService, $this->setExtensionConfiguration(), $inlineAssetsService);
        $response = $middleware->process($this->request, $this->getHandler(new HtmlResponse('original')));

        self::assertSame('replaced', (string) $response->getBody());
    }

    public function testHttpPushHeaders(): void
    {
        $httpPushService = $this->createMock(HttpPushService::class);
        $httpPushService->expects(self::once())
            ->method('getHttpPushHeaders')
            ->with($this->request, 'content')
            ->willReturn([
                ['path' => '/style.css', 'type' => 'style'],
                ['path' => '/font.woff2', 'type' => 'font'],
                ['path' => '/bild-ä.png', 'type' => 'image'],
            ]);

        $middleware = new PrepareMiddleware($this->getEventDispatcher(), $httpPushService, $this->setExtensionConfiguration(), $this->inlineAssetsService);
        $response = $middleware->process($this->request, $this->getHandler(new HtmlResponse('content')));

        self::assertSame([
            '</style.css>; rel=preload; as=style',
            '</font.woff2>; rel=preload; as=font; crossorigin',
        ], $response->getHeader('Link'));
    }

    protected function getMiddleware(?\Psr\EventDispatcher\EventDispatcherInterface $eventDispatcher = null): PrepareMiddleware
    {
        return new PrepareMiddleware(
            $eventDispatcher ?? $this->getEventDispatcher(),
            $this->httpPushService,
            $this->setExtensionConfiguration($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['staticfilecache'] ?? []),
            $this->inlineAssetsService,
        );
    }
}

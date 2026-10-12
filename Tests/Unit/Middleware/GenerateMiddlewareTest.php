<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Middleware;

use PHPUnit\Framework\MockObject\MockObject;
use SFC\Staticfilecache\Event\PreGenerateEvent;
use SFC\Staticfilecache\Middleware\GenerateMiddleware;
use SFC\Staticfilecache\Service\CacheService;
use SFC\Staticfilecache\Service\CookieService;
use TYPO3\CMS\Core\Cache\CacheDataCollector;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Information\Typo3Version;

/**
 * GenerateMiddlewareTest.
 *
 * @internal
 * @coversNothing
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
final class GenerateMiddlewareTest extends AbstractMiddlewareTest
{
    protected FrontendInterface&MockObject $cache;

    protected CacheService&MockObject $cacheService;

    protected CookieService&MockObject $cookieService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cache = $this->createMock(FrontendInterface::class);
        $this->cacheService = $this->createMock(CacheService::class);
        $this->cacheService->method('get')->willReturn($this->cache);
        $this->cookieService = $this->createMock(CookieService::class);
        $this->setExtensionConfiguration();
    }

    public function testNotCachableResponseIsNotCached(): void
    {
        $this->cacheService->expects(self::never())->method('get');

        $response = $this->getMiddleware()->process($this->getRequest(), $this->getHandler(new HtmlResponse('', 200, ['X-SFC-Tags' => 'tag'])));

        self::assertFalse($response->hasHeader('X-SFC-Tags'));
    }

    public function testNonSuccessfulResponseIsNotCached(): void
    {
        $this->cacheService->expects(self::never())->method('get');

        $response = $this->getMiddleware()->process($this->getRequest(), $this->getHandler($this->getCachableResponse(404)));

        self::assertSame(404, $response->getStatusCode());
        self::assertFalse($response->hasHeader('X-SFC-Cachable'));
    }

    public function testCacheErrorsAreIgnored(): void
    {
        $cacheService = $this->createStub(CacheService::class);
        $cacheService->method('get')->willThrowException(new \RuntimeException('No cache'));

        $middleware = new GenerateMiddleware($this->getEventDispatcher(), $this->cookieService, new Typo3Version(), $cacheService, $this->setExtensionConfiguration());
        $response = $middleware->process($this->getRequest(), $this->getHandler($this->getCachableResponse()));

        self::assertSame(200, $response->getStatusCode());
        self::assertFalse($response->hasHeader('X-SFC-Cachable'));
    }

    public function testAddResponseToCache(): void
    {
        $this->cache->method('get')->willReturn(false);
        $this->cache->expects(self::once())
            ->method('set')
            ->with(
                'https://example.com/page/',
                self::callback(static fn(HtmlResponse $response): bool => 'TYPO3 - add to cache' === $response->getHeaderLine('X-SFC-State')),
                ['pageId_1', 'sfc_domain_example_com'],
                3600,
            );

        $response = $this->getMiddleware()->process($this->getRequest(3600), $this->getHandler($this->getCachableResponse()));

        foreach (['X-SFC-Cachable', 'X-SFC-State', 'X-SFC-Explanation', 'X-SFC-Tags'] as $header) {
            self::assertFalse($response->hasHeader($header), $header . ' is removed');
        }
    }

    public function testKeepSfcHeadersInDebugMode(): void
    {
        $this->setExtensionConfiguration(['debugHeaders' => '1']);
        $this->cache->method('get')->willReturn(false);

        $response = $this->getMiddleware()->process($this->getRequest(), $this->getHandler($this->getCachableResponse()));

        self::assertSame('1', $response->getHeaderLine('X-SFC-Cachable'));
        self::assertSame('TYPO3 - add to cache', $response->getHeaderLine('X-SFC-State'));
    }

    public function testResponseWithExplanationIsStoredWithoutLifetime(): void
    {
        $this->setExtensionConfiguration(['debugHeaders' => '1']);
        $this->cache->expects(self::never())->method('get');
        $this->cache->expects(self::once())->method('set')->with(self::anything(), self::anything(), self::anything(), 0);

        $cachableResponse = $this->getCachableResponse()->withHeader('X-SFC-Explanation', 'Not allowed');
        $response = $this->getMiddleware()->process($this->getRequest(), $this->getHandler($cachableResponse));

        self::assertSame('TYPO3 - no cache', $response->getHeaderLine('X-SFC-State'));
    }

    public function testValidCacheEntryIsNotStoredAgain(): void
    {
        $this->setExtensionConfiguration(['debugHeaders' => '1']);
        $this->cache->method('get')->willReturn(['explanation' => [], 'expires' => PHP_INT_MAX]);
        $this->cache->expects(self::never())->method('set');
        $this->cookieService->method('hasCookie')->willReturn(false);

        $response = $this->getMiddleware()->process($this->getRequest(), $this->getHandler($this->getCachableResponse()));

        self::assertSame('TYPO3 - already in cache', $response->getHeaderLine('X-SFC-State'));
    }

    /**
     * @return array<string, array{array{explanation: list<string>, expires: int}, bool}>
     */
    public static function invalidCacheEntryDataProvider(): array
    {
        return [
            'expired entry' => [['explanation' => [], 'expires' => -1], false],
            'entry with explanation' => [['explanation' => ['reason'], 'expires' => PHP_INT_MAX], false],
            'valid entry but cookie' => [['explanation' => [], 'expires' => PHP_INT_MAX], true],
        ];
    }

    /**
     * @param array{explanation: list<string>, expires: int} $entry
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('invalidCacheEntryDataProvider')]
    public function testInvalidCacheEntryIsRegenerated(array $entry, bool $hasCookie): void
    {
        $this->cache->method('get')->willReturn($entry);
        $this->cache->expects(self::once())->method('set');
        $this->cookieService->method('hasCookie')->willReturn($hasCookie);

        $this->getMiddleware()->process($this->getRequest(), $this->getHandler($this->getCachableResponse()));
    }

    public function testEventCanChangeUriAndResponse(): void
    {
        $this->cache->method('get')->willReturn(false);
        $this->cache->expects(self::once())
            ->method('set')
            ->with('https://example.com/changed/', self::callback(static fn(HtmlResponse $response): bool => 'yes' === $response->getHeaderLine('X-Changed')));

        $eventDispatcher = $this->getEventDispatcher(static function (object $event): void {
            if ($event instanceof PreGenerateEvent) {
                $event->setUri('https://example.com/changed/');
                $event->setResponse($event->getResponse()->withHeader('X-Changed', 'yes'));
            }
        });

        $middleware = new GenerateMiddleware($eventDispatcher, $this->cookieService, new Typo3Version(), $this->cacheService, $this->setExtensionConfiguration());
        $response = $middleware->process($this->getRequest(), $this->getHandler($this->getCachableResponse()));

        self::assertSame('yes', $response->getHeaderLine('X-Changed'));
    }

    protected function getCachableResponse(int $status = 200): HtmlResponse
    {
        return new HtmlResponse('<html></html>', $status, [
            'X-SFC-Cachable' => '1',
            'X-SFC-Tags' => ['pageId_1', 'sfc_domain_example_com'],
        ]);
    }

    protected function getRequest(int $lifetime = 86400): ServerRequest
    {
        $collector = new CacheDataCollector();
        $collector->restrictMaximumLifetime($lifetime);

        return (new ServerRequest('https://example.com/page/'))->withAttribute('frontend.cache.collector', $collector);
    }

    protected function getMiddleware(): GenerateMiddleware
    {
        return new GenerateMiddleware(
            $this->getEventDispatcher(),
            $this->cookieService,
            new Typo3Version(),
            $this->cacheService,
            $this->setExtensionConfiguration($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['staticfilecache'] ?? []),
        );
    }
}

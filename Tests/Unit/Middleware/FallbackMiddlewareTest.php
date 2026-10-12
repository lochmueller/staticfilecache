<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Middleware;

use PHPUnit\Framework\MockObject\Stub;
use SFC\Staticfilecache\Cache\IdentifierBuilder;
use SFC\Staticfilecache\Event\CacheRuleFallbackEvent;
use SFC\Staticfilecache\Middleware\FallbackMiddleware;
use SFC\Staticfilecache\Service\CacheService;
use SFC\Staticfilecache\Service\CookieService;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Http\ServerRequest;

/**
 * @internal
 * @coversNothing
 */
final class FallbackMiddlewareTest extends AbstractMiddlewareTest
{
    protected string $cacheDirectory;

    protected string $staticFile;

    protected IdentifierBuilder&Stub $identifierBuilder;

    protected CacheService&Stub $cacheService;

    protected function setUp(): void
    {
        parent::setUp();
        unset($_COOKIE[CookieService::FE_COOKIE_NAME]);
        $this->cacheDirectory = $this->createTemporaryDirectory();
        $this->staticFile = $this->cacheDirectory . 'https_example.com_443/page/index';
        mkdir(\dirname($this->staticFile), 0o777, true);

        $this->identifierBuilder = $this->createStub(IdentifierBuilder::class);
        $this->identifierBuilder->method('getFilepath')->willReturnCallback(fn(): string => $this->staticFile);
        $this->cacheService = $this->createStub(CacheService::class);
        $this->cacheService->method('getAbsoluteBaseDirectory')->willReturnCallback(fn(): string => $this->cacheDirectory);

        $this->setExtensionConfiguration(['useFallbackMiddleware' => '1']);
    }

    public function testMissingConfigurationFileIsIgnoredWithoutWarning(): void
    {
        $subject = (new \ReflectionClass(TestableFallbackMiddleware::class))->newInstanceWithoutConstructor();
        $possibleStaticFile = sys_get_temp_dir() . '/staticfilecache-' . bin2hex(random_bytes(8)) . '/index';

        set_error_handler(
            static function (int $severity, string $message, string $file, int $line): never {
                throw new \ErrorException($message, 0, $severity, $file, $line);
            },
        );

        try {
            self::assertSame([], $subject->getCacheConfigurationForTest($possibleStaticFile));
        } finally {
            restore_error_handler();
        }
    }

    public function testDisabledFallbackCallsHandler(): void
    {
        $this->setExtensionConfiguration(['useFallbackMiddleware' => '0']);
        file_put_contents($this->staticFile, 'cached');

        $response = new HtmlResponse('typo3');
        self::assertSame($response, $this->getMiddleware()->process($this->getRequest(), $this->getHandler($response)));
    }

    public function testDeliverStaticFile(): void
    {
        file_put_contents($this->staticFile, 'cached');
        file_put_contents($this->staticFile . '.config.json', json_encode([
            'invalidAtTimestamp' => time() + 3600,
            'headers' => ['X-Custom' => 'value'],
        ]));

        $handler = $this->getHandler(new HtmlResponse('typo3'));
        $response = $this->getMiddleware()->process($this->getRequest(), $handler);

        self::assertNull($handler->handledRequest);
        self::assertSame(200, $response->getStatusCode());
        self::assertSame('cached', (string) $response->getBody());
        self::assertSame('value', $response->getHeaderLine('X-Custom'));
        self::assertSame('text/html; charset=utf-8', $response->getHeaderLine('Content-Type'));
        self::assertFalse($response->hasHeader('X-SFC-State'));
        self::assertFalse($response->hasHeader('Content-Encoding'));
    }

    public function testDebugHeader(): void
    {
        $this->setExtensionConfiguration(['useFallbackMiddleware' => '1', 'debugHeaders' => '1']);
        file_put_contents($this->staticFile, 'cached');

        $response = $this->getMiddleware()->process($this->getRequest(), $this->getHandler(new HtmlResponse('typo3')));

        self::assertSame('StaticFileCache - via Fallback Middleware', $response->getHeaderLine('X-SFC-State'));
    }

    public static function compressionDataProvider(): array
    {
        return [
            'brotli' => ['gzip, deflate, br', ['br', 'gz'], 'br', 'brotli'],
            'gzip' => ['gzip, deflate', ['br', 'gz'], 'gzip', 'gzip'],
            'gzip fallback without brotli file' => ['gzip, br', ['gz'], 'gzip', 'gzip'],
            'no compressed file' => ['gzip, br', [], '', 'plain'],
            'brotli with q=0' => ['gzip, br;q=0', ['br', 'gz'], 'gzip', 'gzip'],
            'all with q=0' => ['br; q=0, gzip;q=0.0', ['br', 'gz'], '', 'plain'],
            'wildcard' => ['*', ['br', 'gz'], 'br', 'brotli'],
            'wildcard without brotli' => ['br;q=0, *', ['br', 'gz'], 'gzip', 'gzip'],
            'no accept encoding' => ['', ['br', 'gz'], '', 'plain'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('compressionDataProvider')]
    public function testDeliverCompressedFile(string $acceptEncoding, array $existingFiles, string $expectedEncoding, string $expectedContent): void
    {
        file_put_contents($this->staticFile, 'plain');
        foreach ($existingFiles as $extension) {
            file_put_contents($this->staticFile . '.' . $extension, 'br' === $extension ? 'brotli' : 'gzip');
        }

        $request = $this->getRequest()->withHeader('Accept-Encoding', $acceptEncoding);
        $response = $this->getMiddleware()->process($request, $this->getHandler(new HtmlResponse('typo3')));

        self::assertSame($expectedEncoding, $response->getHeaderLine('Content-Encoding'));
        self::assertSame($expectedContent, (string) $response->getBody());
        self::assertSame('Accept-Encoding', $response->getHeaderLine('Vary'));
    }

    public function testSkipProcessingViaEvent(): void
    {
        file_put_contents($this->staticFile, 'cached');
        $eventDispatcher = $this->getEventDispatcher(static function (object $event): void {
            if ($event instanceof CacheRuleFallbackEvent) {
                $event->setSkipProcessing(true);
            }
        });

        $this->assertHandledByTypo3($this->getMiddleware($eventDispatcher));
    }

    public function testFrontendUserCookieSkipsFallback(): void
    {
        file_put_contents($this->staticFile, 'cached');
        $_COOKIE[CookieService::FE_COOKIE_NAME] = 'typo_user_logged_in';

        $this->assertHandledByTypo3($this->getMiddleware());
    }

    public function testExpiredCacheEntrySkipsFallback(): void
    {
        file_put_contents($this->staticFile, 'cached');
        file_put_contents($this->staticFile . '.config.json', json_encode(['invalidAtTimestamp' => time() - 10]));

        $this->assertHandledByTypo3($this->getMiddleware());
    }

    public function testMissingStaticFileSkipsFallback(): void
    {
        $this->assertHandledByTypo3($this->getMiddleware());
    }

    public function testFileOutsideOfCacheDirectorySkipsFallback(): void
    {
        file_put_contents($this->staticFile, 'cached');
        $this->cacheDirectory = $this->cacheDirectory . 'other/';

        $this->assertHandledByTypo3($this->getMiddleware());
    }

    protected function assertHandledByTypo3(FallbackMiddleware $middleware): void
    {
        $response = new HtmlResponse('typo3');
        $handler = $this->getHandler($response);

        self::assertSame($response, $middleware->process($this->getRequest(), $handler));
        self::assertNotNull($handler->handledRequest);
    }

    protected function getRequest(): ServerRequest
    {
        return new ServerRequest('https://example.com/page/?foo=bar');
    }

    protected function getMiddleware(?\Psr\EventDispatcher\EventDispatcherInterface $eventDispatcher = null): FallbackMiddleware
    {
        return new FallbackMiddleware(
            $eventDispatcher ?? $this->getEventDispatcher(),
            $this->setExtensionConfiguration($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['staticfilecache'] ?? []),
            $this->identifierBuilder,
            $this->cacheService,
        );
    }
}

final class TestableFallbackMiddleware extends FallbackMiddleware
{
    public function getCacheConfigurationForTest(string $possibleStaticFile): array
    {
        return parent::getCacheConfiguration($possibleStaticFile);
    }
}

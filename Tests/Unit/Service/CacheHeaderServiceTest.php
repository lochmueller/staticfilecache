<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Service;

use SFC\Staticfilecache\Service\CacheHeaderService;
use SFC\Staticfilecache\Service\DateTimeService;
use SFC\Staticfilecache\Tests\Unit\AbstractTest;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\TypoScript\AST\Node\RootNode;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;

/**
 * CacheHeaderServiceTest.
 *
 * @internal
 * @coversNothing
 */
final class CacheHeaderServiceTest extends AbstractTest
{
    public function testTypoScriptConfigurationWithoutRequest(): void
    {
        self::assertSame(
            ['sendCacheHeaders' => false, 'sendCacheHeadersForSharedCaches' => ''],
            (new CacheHeaderService())->getTypoScriptConfiguration(null)
        );
    }

    public function testTypoScriptConfigurationWithoutFrontendTypoScript(): void
    {
        self::assertSame(
            ['sendCacheHeaders' => false, 'sendCacheHeadersForSharedCaches' => ''],
            (new CacheHeaderService())->getTypoScriptConfiguration(new ServerRequest('https://example.com/'))
        );
    }

    public function testTypoScriptConfigurationFromFrontendTypoScript(): void
    {
        $frontendTypoScript = new FrontendTypoScript(new RootNode(), [], [], []);
        $frontendTypoScript->setConfigArray(['sendCacheHeaders' => '1', 'sendCacheHeadersForSharedCaches' => 'force']);
        $request = (new ServerRequest('https://example.com/'))->withAttribute('frontend.typoscript', $frontendTypoScript);

        self::assertSame(
            ['sendCacheHeaders' => true, 'sendCacheHeadersForSharedCaches' => 'force'],
            (new CacheHeaderService())->getTypoScriptConfiguration($request)
        );
    }

    /**
     * @return array<string, array{string, bool, bool}>
     */
    public static function forSharedCachesDataProvider(): array
    {
        return [
            'force' => ['force', false, true],
            'auto behind a trusted reverse proxy' => ['auto', true, true],
            'auto without a trusted reverse proxy' => ['auto', false, false],
            'not set' => ['', true, false],
            'unknown value' => ['always', true, false],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('forSharedCachesDataProvider')]
    public function testIsForSharedCaches(string $sendCacheHeadersForSharedCaches, bool $behindReverseProxy, bool $expected): void
    {
        $request = $this->getRequest($behindReverseProxy);

        self::assertSame($expected, (new CacheHeaderService())->isForSharedCaches($sendCacheHeadersForSharedCaches, $request));
    }

    public function testIsForSharedCachesCreatesNormalizedParamsWhenMissing(): void
    {
        $previous = $GLOBALS['TYPO3_CONF_VARS']['SYS']['reverseProxyIP'] ?? null;
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['reverseProxyIP'] = '10.0.0.1';
        $request = new ServerRequest('https://example.com/', 'GET', null, [], ['REMOTE_ADDR' => '10.0.0.1']);

        try {
            self::assertTrue((new CacheHeaderService())->isForSharedCaches('auto', $request));
        } finally {
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['reverseProxyIP'] = $previous;
        }
    }

    public function testNoHeadersWithoutSendCacheHeaders(): void
    {
        self::assertSame([], (new CacheHeaderService())->getHeaders(false, false, $this->getNow() + 300));
    }

    public function testHeadersForClients(): void
    {
        $expires = $this->getNow() + 300;

        self::assertSame([
            'Expires' => gmdate('D, d M Y H:i:s T', $expires),
            'Cache-Control' => 'max-age=300',
        ], (new CacheHeaderService())->getHeaders(true, false, $expires));
    }

    public function testHeadersForSharedCachesWithoutSendCacheHeaders(): void
    {
        $expires = $this->getNow() + 300;

        self::assertSame([
            'Expires' => gmdate('D, d M Y H:i:s T', $expires),
            'Cache-Control' => 'max-age=0, s-maxage=300',
        ], (new CacheHeaderService())->getHeaders(false, true, $expires));
    }

    public function testHeadersOfAnExpiredEntryNeverGoNegative(): void
    {
        self::assertSame('max-age=0, s-maxage=0', (new CacheHeaderService())->getHeaders(true, true, $this->getNow() - 10)['Cache-Control']);
    }

    protected function getNow(): int
    {
        return (new DateTimeService())->getCurrentTime();
    }

    protected function getRequest(bool $behindReverseProxy): ServerRequest
    {
        $normalizedParams = NormalizedParams::createFromServerParams(
            ['REMOTE_ADDR' => '10.0.0.1', 'HTTP_HOST' => 'example.com'],
            ['reverseProxyIP' => $behindReverseProxy ? '10.0.0.1' : '']
        );

        return (new ServerRequest('https://example.com/'))->withAttribute('normalizedParams', $normalizedParams);
    }
}

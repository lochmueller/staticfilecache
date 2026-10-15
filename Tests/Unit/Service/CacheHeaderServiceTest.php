<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Service;

use SFC\Staticfilecache\Service\CacheHeaderService;
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

    protected function getRequest(bool $behindReverseProxy): ServerRequest
    {
        $normalizedParams = NormalizedParams::createFromServerParams(
            ['REMOTE_ADDR' => '10.0.0.1', 'HTTP_HOST' => 'example.com'],
            ['reverseProxyIP' => $behindReverseProxy ? '10.0.0.1' : '']
        );

        return (new ServerRequest('https://example.com/'))->withAttribute('normalizedParams', $normalizedParams);
    }
}

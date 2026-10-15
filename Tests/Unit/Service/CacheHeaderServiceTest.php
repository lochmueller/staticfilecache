<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Service;

use SFC\Staticfilecache\Service\CacheHeaderService;
use SFC\Staticfilecache\Tests\Unit\AbstractTest;
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
}

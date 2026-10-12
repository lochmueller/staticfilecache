<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Service;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use SFC\Staticfilecache\Service\CookieService;
use SFC\Staticfilecache\Service\DateTimeService;
use SFC\Staticfilecache\Tests\Unit\AbstractTest;

/**
 * CookieServiceTest.
 *
 * @internal
 * @coversNothing
 */
final class CookieServiceTest extends AbstractTest
{
    public function testHasCookie(): void
    {
        $cookieService = new CookieService(new DateTimeService());

        unset($_COOKIE[CookieService::FE_COOKIE_NAME]);
        self::assertFalse($cookieService->hasCookie());

        $_COOKIE[CookieService::FE_COOKIE_NAME] = 'typo_user_logged_in';
        self::assertTrue($cookieService->hasCookie());
    }

    /**
     * @return array<string, array{string, string, string, string}>
     */
    public static function cookieDomainDataProvider(): array
    {
        return [
            'no domain' => ['', '', 'www.example.com', ''],
            'system domain' => [' .example.com ', '', 'www.example.com', '.example.com'],
            'frontend domain wins' => ['.example.com', '.example.org', 'www.example.com', '.example.org'],
            'regex domain matches' => ['/\.example\.com$/', '', 'www.example.com', '.example.com'],
            'regex domain does not match' => ['/\.example\.org$/', '', 'www.example.com', ''],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('cookieDomainDataProvider')]
    public function testGetCookieDomain(string $systemDomain, string $frontendDomain, string $host, string $expected): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['cookieDomain'] = $systemDomain;
        $GLOBALS['TYPO3_CONF_VARS']['FE']['cookieDomain'] = $frontendDomain;
        $_SERVER['HTTP_HOST'] = $host;

        $cookieService = new CookieService(new DateTimeService());
        $cookieService->setLogger(new NullLogger());

        self::assertSame($expected, $this->getCookieDomain($cookieService));
    }

    public function testGetCookieDomainLogWarningOnInvalidRegex(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['cookieDomain'] = '/(invalid/';
        $GLOBALS['TYPO3_CONF_VARS']['FE']['cookieDomain'] = '';
        $_SERVER['HTTP_HOST'] = 'www.example.com';

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('warning');

        $cookieService = new CookieService(new DateTimeService());
        $cookieService->setLogger($logger);

        self::assertSame('', @$this->getCookieDomain($cookieService));
    }

    protected function getCookieDomain(CookieService $cookieService): string
    {
        return (new \ReflectionMethod($cookieService, 'getCookieDomain'))->invoke($cookieService);
    }
}

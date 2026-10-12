<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Middleware;

use PHPUnit\Framework\MockObject\MockObject;
use SFC\Staticfilecache\Middleware\FrontendUserMiddleware;
use SFC\Staticfilecache\Service\CookieService;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

/**
 * FrontendUserMiddlewareTest.
 *
 * @internal
 * @coversNothing
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
final class FrontendUserMiddlewareTest extends AbstractMiddlewareTest
{
    protected CookieService&MockObject $cookieService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cookieService = $this->createMock(CookieService::class);
        unset($GLOBALS['TYPO3_CONF_VARS']['FE']['cookieName']);
    }

    public function testMissingFrontendUserThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(127389123);

        $this->getMiddleware()->process(new ServerRequest('https://example.com/'), $this->getHandler(new HtmlResponse('')));
    }

    public function testSetSessionCookieOnce(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['FE']['lifetime'] = CookieService::SESSION_LIFETIME;
        $this->cookieService->method('hasCookie')->willReturn(false);
        $this->cookieService->expects(self::once())->method('setCookie')->with(CookieService::SESSION_LIFETIME);
        $this->cookieService->expects(self::never())->method('unsetCookie');

        $response = new HtmlResponse('content');
        $result = $this->getMiddleware()->process($this->getRequest('fe_typo_user=abc; Path=/'), $this->getHandler($response));

        self::assertSame($response, $result);
    }

    public function testSessionCookieIsNotSetTwice(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['FE']['lifetime'] = CookieService::SESSION_LIFETIME;
        $this->cookieService->method('hasCookie')->willReturn(true);
        $this->cookieService->expects(self::never())->method('setCookie');
        $this->cookieService->expects(self::never())->method('unsetCookie');

        $this->getMiddleware()->process($this->getRequest('fe_typo_user=abc; Path=/'), $this->getHandler(new HtmlResponse('')));
    }

    public function testLifetimeCookieIsRefreshed(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['FE']['lifetime'] = 3600;
        $this->cookieService->method('hasCookie')->willReturn(true);
        $this->cookieService->expects(self::once())->method('setCookie')->with(3600);

        $this->getMiddleware()->process($this->getRequest('fe_typo_user=abc; Path=/'), $this->getHandler(new HtmlResponse('')));
    }

    public function testExistingFrontendUserCookieKeepsCookie(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['FE']['lifetime'] = 3600;
        $this->cookieService->expects(self::once())->method('setCookie')->with(3600);

        $request = $this->getRequest('')->withCookieParams(['fe_typo_user' => 'abc']);
        $this->getMiddleware()->process($request, $this->getHandler(new HtmlResponse('')));
    }

    public function testRemoveCookieOnLogout(): void
    {
        $this->cookieService->method('hasCookie')->willReturn(true);
        $this->cookieService->expects(self::never())->method('setCookie');
        $this->cookieService->expects(self::once())->method('unsetCookie');

        $request = $this->getRequest('fe_typo_user=deleted; Path=/; Max-Age=0')->withCookieParams(['fe_typo_user' => 'abc']);
        $this->getMiddleware()->process($request, $this->getHandler(new HtmlResponse('')));
    }

    public function testRemoveCookieWithoutFrontendUserSession(): void
    {
        $this->cookieService->method('hasCookie')->willReturn(true);
        $this->cookieService->expects(self::once())->method('unsetCookie');

        $this->getMiddleware()->process($this->getRequest(''), $this->getHandler(new HtmlResponse('')));
    }

    public function testNothingHappensWithoutAnyCookie(): void
    {
        $this->cookieService->method('hasCookie')->willReturn(false);
        $this->cookieService->expects(self::never())->method('setCookie');
        $this->cookieService->expects(self::never())->method('unsetCookie');

        $this->getMiddleware()->process($this->getRequest(''), $this->getHandler(new HtmlResponse('')));
    }

    protected function getRequest(string $setCookieHeader): ServerRequest
    {
        $response = new HtmlResponse('');
        if ('' !== $setCookieHeader) {
            $response = $response->withHeader('Set-Cookie', $setCookieHeader);
        }

        $frontendUser = $this->createStub(FrontendUserAuthentication::class);
        $frontendUser->method('appendCookieToResponse')->willReturn($response);

        return (new ServerRequest('https://example.com/'))
            ->withAttribute('frontend.user', $frontendUser)
            ->withAttribute('normalizedParams', new NormalizedParams([], [], '', ''));
    }

    protected function getMiddleware(): FrontendUserMiddleware
    {
        return new FrontendUserMiddleware($this->cookieService);
    }
}

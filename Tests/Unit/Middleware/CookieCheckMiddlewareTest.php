<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Middleware;

use SFC\Staticfilecache\Middleware\CookieCheckMiddleware;
use SFC\Staticfilecache\Service\CookieService;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

/**
 * CookieCheckMiddlewareTest.
 *
 * @internal
 * @coversNothing
 */
final class CookieCheckMiddlewareTest extends AbstractMiddlewareTest
{
    public static function cookieDataProvider(): array
    {
        return [
            'no cookie' => [false, false, false, false],
            'cookie without login' => [true, false, false, true],
            'cookie with frontend login' => [true, true, false, false],
            'cookie with backend login' => [true, false, true, false],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('cookieDataProvider')]
    public function testUnsetCookieWithoutValidSession(bool $hasCookie, bool $frontendLogin, bool $backendLogin, bool $expectUnset): void
    {
        $context = new Context();
        if ($frontendLogin) {
            $context->setAspect('frontend.user', new UserAspect($this->getUser(FrontendUserAuthentication::class)));
        }
        if ($backendLogin) {
            $context->setAspect('backend.user', new UserAspect($this->getUser(BackendUserAuthentication::class)));
        }

        $cookieService = $this->createMock(CookieService::class);
        $cookieService->method('hasCookie')->willReturn($hasCookie);
        $cookieService->expects($expectUnset ? self::once() : self::never())->method('unsetCookie');

        $response = new HtmlResponse('content');
        $handler = $this->getHandler($response);
        $request = new ServerRequest('https://example.com/');

        $result = (new CookieCheckMiddleware($context, $cookieService, $this->getEventDispatcher()))->process($request, $handler);

        self::assertSame($response, $result);
        self::assertSame($request, $handler->handledRequest);
    }

    /**
     * @param class-string<FrontendUserAuthentication|BackendUserAuthentication> $className
     */
    protected function getUser(string $className): FrontendUserAuthentication|BackendUserAuthentication
    {
        $user = (new \ReflectionClass($className))->newInstanceWithoutConstructor();
        $user->userid_column = 'uid';
        $user->user = ['uid' => 1];

        return $user;
    }
}

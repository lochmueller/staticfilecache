<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SFC\Staticfilecache\Service\CookieService;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

/**
 * Init frontend user.
 */
class FrontendUserMiddleware implements MiddlewareInterface
{
    protected CookieService $cookieService;

    public function __construct(CookieService $cookieService)
    {
        $this->cookieService = $cookieService;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var FrontendUserAuthentication $feUser */
        $feUser = $request->getAttribute('frontend.user');
        $response = $handler->handle($request);

        if ($feUser->dontSetCookie) {
            // do not set any cookie
            return $response;
        }

        $started = $feUser->loginSessionStarted;

        if (($started || $feUser->forceSetCookie) && $feUser->lifetime >= 0 && !$this->cookieService->hasCookie()) {
            $this->cookieService->setCookie();
        }

        return $response;
    }
}

<?php

/**
 * Init frontend user.
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SFC\Staticfilecache\Service\CookieService;
use SFC\Staticfilecache\Service\DateTimeService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

/**
 * Init frontend user.
 */
class FrontendUserMiddleware implements MiddlewareInterface
{
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

        $cookieService = GeneralUtility::makeInstance(CookieService::class);
        if (($started || $feUser->forceSetCookie) && 0 === $feUser->lifetime) {
            // If new session and the cookie is a sessioncookie, we need to set it only once!
            // // isSetSessionCookie()
            $cookieService->setCookie(0);
        } elseif (($started || isset($_COOKIE[CookieService::FE_COOKIE_NAME])) && $feUser->lifetime > 0) {
            // If it is NOT a session-cookie, we need to refresh it.
            // isRefreshTimeBasedCookie()
            $cookieService->setCookie((new DateTimeService())->getCurrentTime() + $feUser->lifetime);
        }

        return $response;
    }
}

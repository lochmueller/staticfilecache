<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SFC\Staticfilecache\Service\CookieService;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

/**
 * Init frontend user.
 */
class FrontendUserMiddleware implements MiddlewareInterface
{
    public function __construct(private CookieService $cookieService) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $feUser = $request->getAttribute('frontend.user');
        assert($feUser instanceof FrontendUserAuthentication);

        $response = $handler->handle($request);

        $weShouldHaveCookie = $this->weShouldHaveCookie($feUser, $request);
        $lifetime = (int) ($GLOBALS['TYPO3_CONF_VARS']['FE']['lifetime'] ?? 0);

        if ($weShouldHaveCookie) {
            if ($lifetime === CookieService::SESSION_LIFETIME) {
                // only set session cookie once:
                if (!$this->cookieService->hasCookie()) {
                    $this->cookieService->setCookie(CookieService::SESSION_LIFETIME);
                }
            } else {
                // update lifetime cookie now:
                $this->cookieService->setCookie($lifetime);
            }
        } elseif ($this->cookieService->hasCookie()) {
            // remove cookie:
            $this->cookieService->unsetCookie();
        }

        return $response;
    }

    protected function weShouldHaveCookie(FrontendUserAuthentication $feUser, ServerRequestInterface $request): bool
    {
        $setCookieHeader = $feUser->appendCookieToResponse(new HtmlResponse(''))->getHeaderLine('Set-Cookie');

        if (strpos($setCookieHeader, 'Max-Age=0')) {
            // the new cookie is to delete the old cookie:
            return false;
        }
        if ($setCookieHeader) {
            // a new cookie is set:
            return true;
        }

        // there was a cookie:
        return isset($request->getCookieParams()[FrontendUserAuthentication::getCookieName()]);
    }
}

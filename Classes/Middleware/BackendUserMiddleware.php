<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SFC\Staticfilecache\Service\CookieService;
use TYPO3\CMS\Core\Context\Context;

/**
 * Init backend user.
 */
class BackendUserMiddleware implements MiddlewareInterface
{
    protected Context $context;
    protected CookieService $cookieService;

    public function __construct(Context $context, CookieService $cookieService)
    {
        $this->context = $context;
        $this->cookieService = $cookieService;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        if ($this->context->getAspect('backend.user')->isLoggedIn()) {
            if (!$this->cookieService->hasCookie()) {
                // Set cookie to disable staticfilecache in frontend
                $this->cookieService->setCookie();
            }
            return $response;
        }

        // Remove sfc cookie when the backend session is expired
        // todo: should be handled by more generic
        if ($this->cookieService->hasCookie()) {
            $this->cookieService->unsetCookie();
        }
        return $response;
    }
}

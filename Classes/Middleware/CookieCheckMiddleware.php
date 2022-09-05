<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Middleware;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SFC\Staticfilecache\Service\CookieService;
use TYPO3\CMS\Core\Context\Context;

/**
 * CookieCheckMiddleware.
 */
class CookieCheckMiddleware implements MiddlewareInterface
{
    protected Context $context;
    protected CookieService $cookieService;
    protected EventDispatcherInterface $eventDispatcher;

    /**
     * CookieCheckMiddleware constructor.
     */
    public function __construct(Context $context, CookieService $cookieService, EventDispatcherInterface $eventDispatcher)
    {
        $this->context = $context;
        $this->cookieService = $cookieService;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Check for the sfc cookie and remove it when there is no valid user session.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->cookieService->hasCookie() && !$this->context->getAspect('frontend.user')->isLoggedIn() && !$this->context->getAspect('backend.user')->isLoggedIn()) {
            // Remove staticfilecache cookie when no backend or frontend user is logged in
            $this->cookieService->unsetCookie();
        }

        return $handler->handle($request);
    }
}

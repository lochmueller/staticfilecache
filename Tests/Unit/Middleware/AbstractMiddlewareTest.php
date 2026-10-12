<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Middleware;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SFC\Staticfilecache\Tests\Unit\AbstractTest;

/**
 * Abstract middleware test.
 *
 * @internal
 * @coversNothing
 */
abstract class AbstractMiddlewareTest extends AbstractTest
{
    /**
     * Request handler that returns the given response and remembers the handled request.
     */
    protected function getHandler(ResponseInterface $response): RequestHandlerInterface
    {
        return new class ($response) implements RequestHandlerInterface {
            public ?ServerRequestInterface $handledRequest = null;

            public function __construct(private readonly ResponseInterface $response) {}

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $this->handledRequest = $request;

                return $this->response;
            }
        };
    }

    /**
     * Event dispatcher that calls the given listener (if any) for every event.
     */
    protected function getEventDispatcher(?\Closure $listener = null): EventDispatcherInterface
    {
        return new class ($listener) implements EventDispatcherInterface {
            public function __construct(private readonly ?\Closure $listener) {}

            public function dispatch(object $event): object
            {
                if (null !== $this->listener) {
                    ($this->listener)($event);
                }

                return $event;
            }
        };
    }
}

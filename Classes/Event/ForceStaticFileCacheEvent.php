<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Event;

use Psr\Http\Message\ServerRequestInterface;

final class ForceStaticFileCacheEvent
{
    public function __construct(
        private bool $forceStatic,
        private ServerRequestInterface $request
    ) {}

    public function isForceStatic(): bool
    {
        return $this->forceStatic;
    }

    public function setForceStatic(bool $forceStatic): void
    {
        $this->forceStatic = $forceStatic;
    }

    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    public function setRequest(ServerRequestInterface $request): void
    {
        $this->request = $request;
    }
}

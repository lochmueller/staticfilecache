<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Event;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class PreGenerateEvent
{
    public function __construct(
        private string $uri,
        private readonly ServerRequestInterface $request,
        private ResponseInterface $response
    ) {}

    public function getUri(): string
    {
        return $this->uri;
    }

    public function setUri(string $uri): void
    {
        $this->uri = $uri;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    public function setResponse(ResponseInterface $response): void
    {
        $this->response = $response;
    }

    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }
}

<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Event;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class PreGenerateEvent
{
    private string $uri;

    private ServerRequestInterface $request;

    private ResponseInterface $response;

    public function __construct(string $uri, ServerRequestInterface $request, ResponseInterface $response)
    {
        $this->uri = $uri;
        $this->request = $request;
        $this->response = $response;
    }

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

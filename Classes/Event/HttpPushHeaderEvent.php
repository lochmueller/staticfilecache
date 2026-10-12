<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Event;

use Psr\Http\Message\ServerRequestInterface;

final class HttpPushHeaderEvent
{
    public function __construct(
        private array  $headers,
        private string $content,
        private array  $extensions,
        private readonly ServerRequestInterface $request
    ) {}

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function setHeaders(array $headers): void
    {
        $this->headers = $headers;
    }

    public function getExtensions(): array
    {
        return $this->extensions;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }


}

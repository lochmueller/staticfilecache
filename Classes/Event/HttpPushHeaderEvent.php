<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Event;

use Psr\Http\Message\ServerRequestInterface;

final class HttpPushHeaderEvent
{
    /**
     * @param list<array{path: string, type: string}> $headers
     * @param string[] $extensions
     */
    public function __construct(
        private array  $headers,
        private string $content,
        private array  $extensions,
        private readonly ServerRequestInterface $request
    ) {}

    /**
     * @return list<array{path: string, type: string}>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @param list<array{path: string, type: string}> $headers
     */
    public function setHeaders(array $headers): void
    {
        $this->headers = $headers;
    }

    /**
     * @return string[]
     */
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

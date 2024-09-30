<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Event;

final class HttpPushHeaderEvent
{
    public function __construct(
        private array  $headers,
        private string $content,
        private array  $extensions
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


}

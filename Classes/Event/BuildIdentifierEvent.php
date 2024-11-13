<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Event;

final class BuildIdentifierEvent
{
    public function __construct(
        private string $requestUri,
        private array  $parts,
    ) {}

    public function getRequestUri(): string
    {
        return $this->requestUri;
    }

    public function getParts(): array
    {
        return $this->parts;
    }

    public function setParts(array $parts): void
    {
        $this->parts = $parts;
    }


}

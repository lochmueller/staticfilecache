<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Event;

final class BuildIdentifierEvent
{
    /**
     * @param array<string, string> $parts
     */
    public function __construct(
        private string $requestUri,
        private array  $parts,
    ) {}

    public function getRequestUri(): string
    {
        return $this->requestUri;
    }

    /**
     * @return array<string, string>
     */
    public function getParts(): array
    {
        return $this->parts;
    }

    /**
     * @param array<string, string> $parts
     */
    public function setParts(array $parts): void
    {
        $this->parts = $parts;
    }


}

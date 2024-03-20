<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Event;

final class BuildClientEvent
{
    public function __construct(private array $options, private array $httpOptions) {}

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    public function getHttpOptions(): array
    {
        return $this->httpOptions;
    }

    public function setHttpOptions(array $httpOptions): void
    {
        $this->httpOptions = $httpOptions;
    }
}

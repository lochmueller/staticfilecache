<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Event;

final class BuildClientEvent
{
    /**
     * @param array<string, mixed> $options
     * @param array<string, mixed> $httpOptions
     */
    public function __construct(private array $options, private array $httpOptions) {}

    /**
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param array<string, mixed> $options
     */
    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    /**
     * @return array<string, mixed>
     */
    public function getHttpOptions(): array
    {
        return $this->httpOptions;
    }

    /**
     * @param array<string, mixed> $httpOptions
     */
    public function setHttpOptions(array $httpOptions): void
    {
        $this->httpOptions = $httpOptions;
    }
}

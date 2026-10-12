<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Event;

final class GeneratorConfigManipulationEvent
{
    /**
     * @param array<string, mixed> $config
     */
    public function __construct(
        protected array $config,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @param array<string, mixed> $config
     */
    public function setConfig(array $config): void
    {
        $this->config = $config;
    }


}

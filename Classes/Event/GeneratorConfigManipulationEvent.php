<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Event;

final class GeneratorConfigManipulationEvent
{
    public function __construct(
        protected array $config,
    ) {}

    public function getConfig(): array
    {
        return $this->config;
    }

    public function setConfig(array $config): void
    {
        $this->config = $config;
    }


}

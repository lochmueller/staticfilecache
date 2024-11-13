<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Event;

final class GeneratorContentManipulationEvent
{
    public function __construct(
        protected string $content,
    ) {}

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }


}

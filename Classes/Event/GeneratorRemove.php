<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Event;

final class GeneratorRemove
{
    public function __construct(
        protected readonly string            $entryIdentifier,
        protected readonly string            $fileName,
    ) {}

    public function getEntryIdentifier(): string
    {
        return $this->entryIdentifier;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }



}

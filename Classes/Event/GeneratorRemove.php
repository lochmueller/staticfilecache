<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Event;

use Psr\Http\Message\ResponseInterface;

final class GeneratorRemove
{
    public function __construct(
        readonly protected string            $entryIdentifier,
        readonly protected string            $fileName,
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

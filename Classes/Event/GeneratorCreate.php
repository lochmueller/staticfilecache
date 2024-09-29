<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Event;

use Psr\Http\Message\ResponseInterface;

final class GeneratorCreate
{
    public function __construct(
        readonly protected string            $entryIdentifier,
        readonly protected string            $fileName,
        readonly protected ResponseInterface $response,
        readonly protected int               $lifetime
    ) {}

    public function getEntryIdentifier(): string
    {
        return $this->entryIdentifier;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    public function getLifetime(): int
    {
        return $this->lifetime;
    }


}

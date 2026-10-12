<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Event;

use Psr\Http\Message\ResponseInterface;

final class GeneratorCreate
{
    /**
     * Content after the GeneratorContentManipulationEvent (shared by all generators).
     */
    protected ?string $manipulatedContent = null;

    public function __construct(
        protected readonly string            $entryIdentifier,
        protected readonly string            $fileName,
        protected readonly ResponseInterface $response,
        protected readonly int               $lifetime
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

    public function getManipulatedContent(): ?string
    {
        return $this->manipulatedContent;
    }

    public function setManipulatedContent(string $manipulatedContent): void
    {
        $this->manipulatedContent = $manipulatedContent;
    }


}

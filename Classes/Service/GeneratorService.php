<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Service;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use SFC\Staticfilecache\Event\GeneratorCreate;
use SFC\Staticfilecache\Event\GeneratorRemove;

class GeneratorService
{
    public function __construct(
        protected EventDispatcherInterface $eventDispatcher,
    ) {}

    public function generate(string $entryIdentifier, string $fileName, ResponseInterface &$response, int $lifetime): void
    {
        $this->eventDispatcher->dispatch(new GeneratorCreate($entryIdentifier, $fileName, $response, $lifetime));
    }

    public function remove(string $entryIdentifier, string $fileName): void
    {
        $this->eventDispatcher->dispatch(new GeneratorRemove($entryIdentifier, $fileName));
    }
}

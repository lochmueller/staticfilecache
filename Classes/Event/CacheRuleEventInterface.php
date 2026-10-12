<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Event;

use Psr\Http\Message\ServerRequestInterface;

interface CacheRuleEventInterface
{
    public function getRequest(): ServerRequestInterface;

    /**
     * @return array<string, string>
     */
    public function getExplanation(): array;

    public function isSkipProcessing(): bool;

    public function setSkipProcessing(bool $state): void;

    public function addExplanation(string $key, string $message): void;

    public function truncateExplanations(): void;
}

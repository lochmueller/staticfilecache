<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Event;

use Psr\Http\Message\ServerRequestInterface;

final class CacheRuleFallbackEvent implements CacheRuleEventInterface
{
    private ServerRequestInterface $request;

    private array $explanation;

    private bool $skipProcessing;

    public function __construct(ServerRequestInterface $request, array $explanation, bool $skipProcessing)
    {
        $this->request = $request;
        $this->explanation = $explanation;
        $this->skipProcessing = $skipProcessing;
    }

    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    public function getExplanation(): array
    {
        return $this->explanation;
    }

    public function isSkipProcessing(): bool
    {
        return $this->skipProcessing;
    }

    public function setSkipProcessing(bool $state): void
    {
        $this->skipProcessing = $state;
    }

    public function addExplanation(string $key, string $message): void
    {
        $this->explanation[$key] = $message;
    }

    public function truncateExplanations(): void
    {
        $this->explanation = [];
    }
}

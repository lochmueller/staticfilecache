<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Event;

use Psr\Http\Message\ServerRequestInterface;

interface CacheRuleEventInterface
{
    public function getRequest(): ServerRequestInterface;

    public function getExplanation();

    public function isSkipProcessing();

    public function setSkipProcessing(bool $state);

    public function addExplanation(string $key, string $message);

    public function truncateExplanations();
}

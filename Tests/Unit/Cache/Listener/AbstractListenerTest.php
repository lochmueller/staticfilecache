<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Cache\Listener;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SFC\Staticfilecache\Event\CacheRuleEvent;
use SFC\Staticfilecache\Tests\Unit\AbstractTest;
use TYPO3\CMS\Core\Http\ServerRequest;

abstract class AbstractListenerTest extends AbstractTest
{
    protected function emptyCacheRuleEvent(): CacheRuleEvent
    {
        return new CacheRuleEvent(
            new ServerRequest(),
            [],
            false,
            $this->createStub(ResponseInterface::class),
        );
    }

    /**
     * @param array<string, string> $explanation
     */
    protected function cacheRuleEvent(
        ServerRequestInterface $request,
        ?ResponseInterface $response = null,
        array $explanation = [],
        bool $skipProcessing = false,
    ): CacheRuleEvent {
        return new CacheRuleEvent(
            $request,
            $explanation,
            $skipProcessing,
            $response ?? $this->createStub(ResponseInterface::class),
        );
    }
}

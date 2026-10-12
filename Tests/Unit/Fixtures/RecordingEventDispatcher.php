<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Fixtures;

use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Event dispatcher that records all dispatched events.
 *
 * @internal
 */
class RecordingEventDispatcher implements EventDispatcherInterface
{
    /**
     * @var object[]
     */
    public array $events = [];

    public function dispatch(object $event): object
    {
        $this->events[] = $event;

        return $event;
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $className
     *
     * @return list<T>
     */
    public function getEvents(string $className): array
    {
        return array_values(array_filter($this->events, static fn(object $event): bool => $event instanceof $className));
    }
}

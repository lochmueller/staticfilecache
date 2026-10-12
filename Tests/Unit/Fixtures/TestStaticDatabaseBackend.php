<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Fixtures;

use SFC\Staticfilecache\Cache\StaticDatabaseBackend;

/**
 * Concrete StaticDatabaseBackend that exposes internals for tests.
 *
 * @internal
 */
class TestStaticDatabaseBackend extends StaticDatabaseBackend
{
    public function getTableNames(): array
    {
        return [$this->cacheTable, $this->tagsTable];
    }

    public function realLifetime(?int $lifetime): int
    {
        return $this->getRealLifetime($lifetime);
    }
}

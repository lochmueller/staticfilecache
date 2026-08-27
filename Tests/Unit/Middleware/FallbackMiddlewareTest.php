<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Middleware;

use SFC\Staticfilecache\Middleware\FallbackMiddleware;
use SFC\Staticfilecache\Tests\Unit\AbstractTest;

/**
 * @internal
 * @coversNothing
 */
final class FallbackMiddlewareTest extends AbstractTest
{
    public function testMissingConfigurationFileIsIgnoredWithoutWarning(): void
    {
        $subject = (new \ReflectionClass(TestableFallbackMiddleware::class))->newInstanceWithoutConstructor();
        $possibleStaticFile = sys_get_temp_dir() . '/staticfilecache-' . bin2hex(random_bytes(8)) . '/index';

        set_error_handler(
            static function (int $severity, string $message, string $file, int $line): never {
                throw new \ErrorException($message, 0, $severity, $file, $line);
            },
        );

        try {
            self::assertSame([], $subject->getCacheConfigurationForTest($possibleStaticFile));
        } finally {
            restore_error_handler();
        }
    }
}

final class TestableFallbackMiddleware extends FallbackMiddleware
{
    public function getCacheConfigurationForTest(string $possibleStaticFile): array
    {
        return parent::getCacheConfiguration($possibleStaticFile);
    }
}

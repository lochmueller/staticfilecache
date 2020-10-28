<?php

/**
 * IdentifierBuilderTest.
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Cache;

use SFC\Staticfilecache\Cache\IdentifierBuilder;
use SFC\Staticfilecache\Tests\Unit\AbstractTest;

/**
 * IdentifierBuilderTest.
 *
 * @internal
 * @coversNothing
 */
final class IdentifierBuilderTest extends AbstractTest
{
    /**
     * Test a valid path.
     */
    public function testCheckValidPath(): void
    {
        $validUris = [
            'https://www.domain.tld/path.html',
            'https://www.example.pl/',
            'https://www.example.pl:8080/',
        ];

        foreach ($validUris as $uri) {
            $identBuilder = new IdentifierBuilder();
            static::assertTrue($identBuilder->isValidEntryIdentifier($uri), 'The URI "'.$uri.'" should be valid!');
        }
    }

    /**
     * Test check invalid path.
     */
    public function testCheckInValidPath(): void
    {
        $invalidUris = [
            '/path.html',
        ];

        foreach ($invalidUris as $uri) {
            $identBuilder = new IdentifierBuilder();
            static::assertFalse($identBuilder->isValidEntryIdentifier($uri), 'The URI "'.$uri.'" should be invalid!');
        }
    }
}

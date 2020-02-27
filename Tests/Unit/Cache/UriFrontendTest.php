<?php

/**
 * Test the URI frontend.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Tests\Unit\Cache;

use SFC\Staticfilecache\Cache\UriFrontend;
use SFC\Staticfilecache\Tests\Unit\AbstractTest;
use TYPO3\CMS\Core\Cache\Backend\NullBackend;

/**
 * Test the URI frontend.
 *
 * @internal
 * @coversNothing
 */
class UriFrontendTest extends AbstractTest
{
    /**
     * Test a valid path
     */
    public function testCheckValidPath()
    {
        $this->resetSingletonInstances = true;

        $validUris = [
            'https://www.domain.tld/path.html',
            'https://www.example.pl/',
            'https://www.example.pl:8080/',
        ];

        foreach ($validUris as $uri) {
            self::assertTrue($this->getCacheFrontend()->isValidEntryIdentifier($uri), 'The URI "' . $uri . '" should be valid!');
        }
    }

    /**
     * Test check invalid path
     */
    public function testCheckInValidPath()
    {
        $this->resetSingletonInstances = true;

        $invalidUris = [
            '/path.html',
        ];

        foreach ($invalidUris as $uri) {
            self::assertFalse($this->getCacheFrontend()->isValidEntryIdentifier($uri), 'The URI "' . $uri . '" should be invalid!');
        }
    }

    /**
     * Get the subject.
     *
     * @return UriFrontend
     */
    protected function getCacheFrontend()
    {
        return new UriFrontend('test', new NullBackend(''));
    }
}

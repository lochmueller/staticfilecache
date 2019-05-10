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
        $this->markTestSkipped();
        $this->assertTrue($this->getCacheFrontend()
            ->isValidEntryIdentifier('https://www.domain.tld/path.html'));
    }

    /**
     * Test check invalid path
     */
    public function testCheckInValidPath()
    {
        $this->assertFalse($this->getCacheFrontend()
            ->isValidEntryIdentifier('/path.html'));
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

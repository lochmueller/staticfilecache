<?php

/**
 * Test the URI frontend.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Tests\Unit\Cache;

use SFC\Staticfilecache\Cache\UriFrontend;
use TYPO3\CMS\Core\Cache\Backend\NullBackend;

/**
 * Test the URI frontend.
 *
 * @internal
 * @coversNothing
 */
class UriFrontendTest extends \PHPUnit\Framework\TestCase
{
    public function testCheckValidPath()
    {
        $this->assertTrue($this->getCacheFrontend()
            ->isValidEntryIdentifier('http://www.domain.tld/path.html'));
    }

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

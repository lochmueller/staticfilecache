<?php

declare(strict_types=1);
/**
 * Test the URI frontend.
 */
namespace SFC\Staticfilecache\Tests\Unit\Cache;

use SFC\Staticfilecache\Cache\UriFrontend;
use TYPO3\CMS\Core\Cache\Backend\NullBackend;

/**
 * Test the URI frontend.
 */
class UriFrontendTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function checkValidPath()
    {
        $this->assertSame(true, $this->getCacheFrontend()
            ->isValidEntryIdentifier('http://www.domain.tld/path.html'));
    }

    /**
     * @test
     */
    public function checkInValidPath()
    {
        $this->assertSame(false, $this->getCacheFrontend()
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

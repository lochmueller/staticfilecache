<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Service;

use SFC\Staticfilecache\Service\HtaccessConfigurationService;
use SFC\Staticfilecache\Tests\Unit\AbstractTest;
use TYPO3\CMS\Core\Core\Environment;

/**
 * HtaccessConfigurationServiceTest.
 *
 * @internal
 * @coversNothing
 */
final class HtaccessConfigurationServiceTest extends AbstractTest
{
    public function testGetHtaccessPaths(): void
    {
        self::assertSame([Environment::getPublicPath() . '/.htaccess'], (new HtaccessConfigurationService())->getHtaccessPaths());
    }

    public function testFoundConfigurationInHtaccess(): void
    {
        $directory = $this->createTemporaryDirectory();
        file_put_contents($directory . 'without', "RewriteEngine On\n");
        file_put_contents($directory . 'with', "RewriteEngine On\nRewriteRule .* - [E=SFC_FULLPATH:typo3temp]\n");

        self::assertTrue($this->getService([$directory . 'missing', $directory . 'without', $directory . 'with'])->foundConfigurationInHtaccess());
    }

    public function testNotFoundConfigurationInHtaccess(): void
    {
        $directory = $this->createTemporaryDirectory();
        file_put_contents($directory . 'without', "RewriteEngine On\n");

        self::assertFalse($this->getService([$directory . 'missing', $directory . 'without'])->foundConfigurationInHtaccess());
    }

    public function testMissingApacheModules(): void
    {
        if (\function_exists('apache_get_modules')) {
            self::markTestSkipped('Test is only valid without apache SAPI');
        }

        self::assertSame([], (new HtaccessConfigurationService())->getMissingApacheModules());
    }

    /**
     * @param list<string> $paths
     */
    protected function getService(array $paths): HtaccessConfigurationService
    {
        $service = $this->getMockBuilder(HtaccessConfigurationService::class)->onlyMethods(['getHtaccessPaths'])->getMock();
        $service->expects(self::once())->method('getHtaccessPaths')->willReturn($paths);

        return $service;
    }
}

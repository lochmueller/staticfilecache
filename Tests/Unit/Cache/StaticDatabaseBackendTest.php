<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Cache;

use SFC\Staticfilecache\Tests\Unit\AbstractTest;
use SFC\Staticfilecache\Tests\Unit\Fixtures\TestStaticDatabaseBackend;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Package\PackageInterface;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * StaticDatabaseBackendTest.
 *
 * @internal
 * @coversNothing
 */
final class StaticDatabaseBackendTest extends AbstractTest
{
    private ?PackageManager $originalPackageManager = null;

    protected function tearDown(): void
    {
        if (null !== $this->originalPackageManager) {
            ExtensionManagementUtility::setPackageManager($this->originalPackageManager);
            $this->originalPackageManager = null;
        }
        parent::tearDown();
    }

    public function testDefaultTableNames(): void
    {
        $this->setExtensionConfiguration();
        $backend = $this->getBackend();

        self::assertSame(['cache_staticfilecache', 'cache_staticfilecache_tags'], $backend->getTableNames());
    }

    public function testRenamedTableNames(): void
    {
        $this->setExtensionConfiguration(['renameTablesToOtherPrefix' => '1']);
        $backend = $this->getBackend();

        self::assertSame(['sfc_staticfilecache', 'sfc_staticfilecache_tags'], $backend->getTableNames());
    }

    public function testGetTableDefinitions(): void
    {
        $this->setExtensionConfiguration();
        $this->registerExtensionPath();
        $definitions = $this->getBackend()->getTableDefinitions();

        self::assertStringContainsString('CREATE TABLE cache_staticfilecache (', $definitions);
        self::assertStringContainsString('CREATE TABLE cache_staticfilecache_tags (', $definitions);
        self::assertStringContainsString('identifier varchar(500)', $definitions);
        self::assertStringNotContainsString('###', $definitions);
    }

    public function testGetLargeTableDefinitions(): void
    {
        $this->setExtensionConfiguration(['largeIdentifierInCacheTable' => '1', 'renameTablesToOtherPrefix' => '1']);
        $this->registerExtensionPath();
        $definitions = $this->getBackend()->getTableDefinitions();

        self::assertStringContainsString('CREATE TABLE sfc_staticfilecache (', $definitions);
        self::assertStringContainsString('CREATE TABLE sfc_staticfilecache_tags (', $definitions);
        self::assertStringContainsString('identifier varchar(10000)', $definitions);
    }

    public function testRealLifetime(): void
    {
        $this->setExtensionConfiguration();
        $backend = $this->getBackend();
        $maximumLifetime = $backend->realLifetime(0);

        self::assertSame(100, $backend->realLifetime(null));
        self::assertSame(50, $backend->realLifetime(50));
        self::assertGreaterThan(100, $maximumLifetime);
        self::assertSame($maximumLifetime, $backend->realLifetime($maximumLifetime + 1));
    }

    private function registerExtensionPath(): void
    {
        $this->originalPackageManager = (new \ReflectionProperty(ExtensionManagementUtility::class, 'packageManager'))->getValue();

        $package = $this->createStub(PackageInterface::class);
        $package->method('getPackagePath')->willReturn(\dirname(__DIR__, 3) . '/');
        $packageManager = $this->createStub(PackageManager::class);
        $packageManager->method('isPackageActive')->willReturn(true);
        $packageManager->method('getPackage')->willReturn($package);
        ExtensionManagementUtility::setPackageManager($packageManager);
    }

    private function getBackend(): TestStaticDatabaseBackend
    {
        $backend = new TestStaticDatabaseBackend(['defaultLifetime' => 100]);
        $cache = $this->createStub(FrontendInterface::class);
        $cache->method('getIdentifier')->willReturn('staticfilecache');
        $backend->setCache($cache);

        return $backend;
    }
}

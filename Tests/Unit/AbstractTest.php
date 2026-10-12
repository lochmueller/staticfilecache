<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit;

use Doctrine\DBAL\DriverManager;
use SFC\Staticfilecache\Service\ConfigurationService;
use SFC\Staticfilecache\Tests\Unit\Fixtures\SqliteConnection;
use Symfony\Component\DependencyInjection\Container;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Abstract test.
 *
 * @internal
 * @coversNothing
 */
abstract class AbstractTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    private bool $databaseCreated = false;

    private bool $containerRegistered = false;

    /**
     * Register a real ConfigurationService (singleton) based on the given extension configuration.
     */
    protected function setExtensionConfiguration(array $configuration = []): ConfigurationService
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['staticfilecache'] = array_merge(['validHtaccessHeaders' => ''], $configuration);
        $configurationService = new ConfigurationService();
        GeneralUtility::setSingletonInstance(ConfigurationService::class, $configurationService);

        return $configurationService;
    }

    /**
     * Create a temporary directory (incl. ending slash) that is removed after the test.
     */
    protected function createTemporaryDirectory(): string
    {
        $directory = Environment::getVarPath() . '/tests/sfc_' . bin2hex(random_bytes(8)) . '/';
        GeneralUtility::mkdir_deep($directory);
        $this->testFilesToDelete[] = $directory;

        return $directory;
    }

    /**
     * Register an in-memory SQLite database as default connection and execute the given SQL statements.
     */
    protected function createDatabase(string ...$statements): Connection
    {
        /** @var SqliteConnection $connection */
        $connection = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'memory' => true,
            'wrapperClass' => SqliteConnection::class,
        ]);
        foreach ($statements as $statement) {
            $connection->executeStatement($statement);
        }

        $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default'] = ['driver' => 'pdo_sqlite', 'memory' => true];
        (new \ReflectionProperty(ConnectionPool::class, 'connections'))->setValue(null, ['Default' => $connection]);
        $this->databaseCreated = true;

        return $connection;
    }

    /**
     * Register a DI container with the given services (service id => instance) for GeneralUtility::makeInstance().
     */
    protected function registerContainerServices(array $services): void
    {
        $container = new Container();
        foreach ($services as $id => $service) {
            $container->set($id, $service);
        }
        GeneralUtility::setContainer($container);
        $this->containerRegistered = true;
    }

    protected function tearDown(): void
    {
        if ($this->databaseCreated) {
            (new ConnectionPool())->resetConnections();
        }
        $containerRegistered = $this->containerRegistered;

        parent::tearDown();

        if ($containerRegistered) {
            // The container is not reset by "resetSingletonInstances"
            GeneralUtility::purgeInstances();
        }
    }
}

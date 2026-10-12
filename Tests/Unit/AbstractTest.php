<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit;

use SFC\Staticfilecache\Service\ConfigurationService;
use TYPO3\CMS\Core\Core\Environment;
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
}

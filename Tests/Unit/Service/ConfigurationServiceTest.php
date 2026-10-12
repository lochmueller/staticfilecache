<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Service;

use SFC\Staticfilecache\Service\ConfigurationService;
use SFC\Staticfilecache\Tests\Unit\AbstractTest;

/**
 * ConfigurationServiceTest.
 *
 * @internal
 * @coversNothing
 */
final class ConfigurationServiceTest extends AbstractTest
{
    public function testThrowExceptionOnOutdatedExtensionConfiguration(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['staticfilecache'] = ['boostMode' => '1'];

        $this->expectException(\Exception::class);
        new ConfigurationService();
    }

    public function testGetValues(): void
    {
        $configurationService = $this->setExtensionConfiguration(['boostMode' => '1', 'number' => 5]);

        self::assertSame('1', $configurationService->get('boostMode'));
        self::assertSame('5', $configurationService->get('number'));
        self::assertNull($configurationService->get('notExisting'));
    }

    public function testGetValueFromTsfeConfiguration(): void
    {
        $configurationService = $this->setExtensionConfiguration();
        $GLOBALS['TSFE'] = new \stdClass();
        $GLOBALS['TSFE']->config = ['config' => ['tx_staticfilecache.' => ['fromTsfe' => 'yes']]];

        self::assertSame('yes', $configurationService->get('fromTsfe'));
    }

    public function testMergeTsfeTemplateSetup(): void
    {
        $GLOBALS['TSFE'] = new \stdClass();
        $GLOBALS['TSFE']->tmpl = new \stdClass();
        $GLOBALS['TSFE']->tmpl->setup = ['tx_staticfilecache.' => ['boostMode' => 'fromTemplate']];

        $configurationService = $this->setExtensionConfiguration(['boostMode' => 'fromExtension']);

        self::assertSame('fromTemplate', $configurationService->get('boostMode'));
    }

    public function testOverrideAndReset(): void
    {
        $configurationService = $this->setExtensionConfiguration(['boostMode' => '1']);

        $configurationService->override('boostMode', '0');
        self::assertSame('0', $configurationService->get('boostMode'));

        $configurationService->reset('boostMode');
        self::assertSame('1', $configurationService->get('boostMode'));

        // Reset of a key without override is no problem
        $configurationService->reset('boostMode');
        self::assertSame('1', $configurationService->get('boostMode'));
    }

    public function testGetAll(): void
    {
        $configurationService = $this->setExtensionConfiguration(['boostMode' => '1']);

        self::assertSame(['validHtaccessHeaders' => '', 'boostMode' => '1'], $configurationService->getAll());
    }

    public function testGetValidHeaders(): void
    {
        $configurationService = $this->setExtensionConfiguration(['validHtaccessHeaders' => 'X-First, X-Second']);

        $result = $configurationService->getValidHeaders([
            'X-First' => ['a', 'b'],
            'X-Second' => ['c'],
            'X-Invalid' => ['d'],
            'Content-Type' => ['text/html'],
        ], 'validHtaccessHeaders');

        self::assertSame([
            'X-First' => 'a, b',
            'X-Second' => 'c',
            'Content-Type' => 'text/html',
        ], $result);
    }

    /**
     * @return array<string, array{string|null, string}>
     */
    public static function backendDisplayModeDataProvider(): array
    {
        return [
            'current' => ['current', 'current'],
            'childs' => ['childs', 'childs'],
            'both' => ['both', 'both'],
            'invalid' => ['invalid', 'current'],
            'not set' => [null, 'current'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('backendDisplayModeDataProvider')]
    public function testGetBackendDisplayMode(?string $configured, string $expected): void
    {
        $configurationService = $this->setExtensionConfiguration(null === $configured ? [] : ['backendDisplayMode' => $configured]);

        self::assertSame($expected, $configurationService->getBackendDisplayMode());
    }

    public function testIsBool(): void
    {
        $configurationService = $this->setExtensionConfiguration(['enabled' => '1', 'disabled' => '0']);

        self::assertTrue($configurationService->isBool('enabled'));
        self::assertFalse($configurationService->isBool('disabled'));
        self::assertFalse($configurationService->isBool('notExisting'));
    }
}

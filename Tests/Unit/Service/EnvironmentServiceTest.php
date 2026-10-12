<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Service;

use SFC\Staticfilecache\Service\EnvironmentService;
use SFC\Staticfilecache\Tests\Unit\AbstractTest;

/**
 * EnvironmentServiceTest.
 *
 * @internal
 * @coversNothing
 */
final class EnvironmentServiceTest extends AbstractTest
{
    public function testGetMarkdown(): void
    {
        $expected = implode("\n", [
            '... Add your description here ...',
            '',
            '',
            '**Environment**',
            '* **PHP Version**: 8.3',
            '* **boostMode**: 1',
        ]);

        self::assertSame($expected, $this->getEnvironmentService()->getMarkdown());
    }

    public function testGetLink(): void
    {
        $environmentService = $this->getEnvironmentService();

        self::assertSame(rawurlencode($environmentService->getMarkdown()), $environmentService->getLink());
    }

    protected function getEnvironmentService(): EnvironmentService
    {
        $environmentService = $this->getMockBuilder(EnvironmentService::class)->onlyMethods(['get'])->getMock();
        $environmentService->expects(self::atLeastOnce())->method('get')->willReturn([
            'PHP Version' => '8.3',
            'boostMode' => '1',
        ]);

        return $environmentService;
    }
}

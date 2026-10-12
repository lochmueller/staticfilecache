<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Service;

use Psr\EventDispatcher\EventDispatcherInterface;
use SFC\Staticfilecache\Event\GeneratorCreate;
use SFC\Staticfilecache\Event\GeneratorRemove;
use SFC\Staticfilecache\Service\GeneratorService;
use SFC\Staticfilecache\Tests\Unit\AbstractTest;
use TYPO3\CMS\Core\Http\HtmlResponse;

/**
 * GeneratorServiceTest.
 *
 * @internal
 * @coversNothing
 */
final class GeneratorServiceTest extends AbstractTest
{
    public function testGenerateDispatchCreateEvent(): void
    {
        $response = new HtmlResponse('content');

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(static fn(GeneratorCreate $event): bool => 'identifier' === $event->getEntryIdentifier()
                && '/path/index.html' === $event->getFileName()
                && $response === $event->getResponse()
                && 3600 === $event->getLifetime()))
            ->willReturnArgument(0);

        (new GeneratorService($eventDispatcher))->generate('identifier', '/path/index.html', $response, 3600);
    }

    public function testRemoveDispatchRemoveEvent(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(static fn(GeneratorRemove $event): bool => 'identifier' === $event->getEntryIdentifier()
                && '/path/index.html' === $event->getFileName()))
            ->willReturnArgument(0);

        (new GeneratorService($eventDispatcher))->remove('identifier', '/path/index.html');
    }
}

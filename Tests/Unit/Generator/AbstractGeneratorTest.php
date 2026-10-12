<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Generator;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use SFC\Staticfilecache\Event\GeneratorContentManipulationEvent;
use SFC\Staticfilecache\Event\GeneratorCreate;
use SFC\Staticfilecache\Event\GeneratorRemove;
use SFC\Staticfilecache\Tests\Unit\AbstractTest;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;
use TYPO3\CMS\Core\View\ViewInterface;

/**
 * AbstractGeneratorTest.
 */
abstract class AbstractGeneratorTest extends AbstractTest
{
    /**
     * Variables assigned to the view of the last rendering.
     *
     * @var array<string, mixed>|null
     */
    protected ?array $assignedVariables = null;

    /**
     * Template file used by the last rendering.
     */
    protected ?string $renderedTemplate = null;

    /**
     * Event dispatcher that returns the event (content events get a prefix to verify the manipulation is used).
     */
    protected function getEventDispatcher(): EventDispatcherInterface
    {
        $eventDispatcher = $this->createStub(EventDispatcherInterface::class);
        $eventDispatcher->method('dispatch')->willReturnCallback(static function (object $event): object {
            if ($event instanceof GeneratorContentManipulationEvent) {
                $event->setContent('manipulated:' . $event->getContent());
            }

            return $event;
        });

        return $eventDispatcher;
    }

    /**
     * View factory that records the assigned variables and renders the given content.
     */
    protected function getViewFactory(string $renderedContent = ' rendered content '): ViewFactoryInterface
    {
        $view = $this->createStub(ViewInterface::class);
        $view->method('assignMultiple')->willReturnCallback(function (array $values) use ($view): ViewInterface {
            $this->assignedVariables = $values;

            return $view;
        });
        $view->method('render')->willReturn($renderedContent);

        $viewFactory = $this->createStub(ViewFactoryInterface::class);
        $viewFactory->method('create')->willReturnCallback(function (ViewFactoryData $data) use ($view): ViewInterface {
            $this->renderedTemplate = $data->templatePathAndFilename;

            return $view;
        });

        return $viewFactory;
    }

    /**
     * @param array<string, string|string[]> $headers
     */
    protected function getResponse(string $body = '<html>Test</html>', array $headers = []): ResponseInterface
    {
        return new HtmlResponse($body, 200, $headers);
    }

    protected function getCreateEvent(string $fileName, ?ResponseInterface $response = null, int $lifetime = 3600): GeneratorCreate
    {
        return new GeneratorCreate('https://example.com/', $fileName, $response ?? $this->getResponse(), $lifetime);
    }

    protected function getRemoveEvent(string $fileName): GeneratorRemove
    {
        return new GeneratorRemove('https://example.com/', $fileName);
    }
}

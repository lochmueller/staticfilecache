<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Generator;

use Psr\EventDispatcher\EventDispatcherInterface;
use SFC\Staticfilecache\Event\GeneratorContentManipulationEvent;
use SFC\Staticfilecache\Event\GeneratorCreate;
use SFC\Staticfilecache\Event\GeneratorRemove;
use SFC\Staticfilecache\Service\ConfigurationService;
use SFC\Staticfilecache\Service\RemoveService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\View\ViewFactoryInterface;
use TYPO3\CMS\Core\View\ViewFactoryData;

abstract class AbstractGenerator
{
    public function __construct(
        protected EventDispatcherInterface $eventDispatcher,
        protected ViewFactoryInterface $viewFactory,
    ) {}

    abstract public function generate(GeneratorCreate $generatorCreateEvent): void;

    abstract public function remove(GeneratorRemove $generatorRemoveEvent): void;

    protected function getConfigurationService(): ConfigurationService
    {
        return GeneralUtility::makeInstance(ConfigurationService::class);
    }

    /**
     * Get the manipulated content. The GeneratorContentManipulationEvent is dispatched only once per GeneratorCreate event.
     */
    protected function getContent(GeneratorCreate $generatorCreateEvent): string
    {
        $content = $generatorCreateEvent->getManipulatedContent();
        if (null === $content) {
            /** @var GeneratorContentManipulationEvent $contentManipulationEvent */
            $contentManipulationEvent = $this->eventDispatcher->dispatch(new GeneratorContentManipulationEvent((string) $generatorCreateEvent->getResponse()->getBody()));
            $content = $contentManipulationEvent->getContent();
            $generatorCreateEvent->setManipulatedContent($content);
        }

        return $content;
    }

    protected function writeFile(string $fileName, string $content): void
    {
        GeneralUtility::writeFile($fileName, $content);
    }

    protected function removeFile(string $fileName): void
    {
        $removeService = GeneralUtility::makeInstance(RemoveService::class);
        $removeService->file($fileName);
    }

    /**
     * @param array<string, mixed> $variables
     */
    protected function renderTemplateToFile(string $templateName, array $variables, string $htaccessFile): void
    {
        $view = $this->viewFactory->create(new ViewFactoryData(
            templatePathAndFilename: GeneralUtility::getFileAbsFileName($templateName),
        ));
        $view->assignMultiple($variables);
        $content = trim($view->render());

        // Note: Create even empty htaccess files (do not check!!!), so the delete is in sync
        $this->writeFile($htaccessFile, $content);
    }
}

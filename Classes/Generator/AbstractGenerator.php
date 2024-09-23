<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Generator;

use Psr\Http\Message\ResponseInterface;
use SFC\Staticfilecache\Service\RemoveService;
use SFC\Staticfilecache\StaticFileCacheObject;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * @todo move to Generate Event
 */
abstract class AbstractGenerator extends StaticFileCacheObject
{
    abstract public function generate(string $entryIdentifier, string $fileName, ResponseInterface $response, int $lifetime): void;

    abstract public function remove(string $entryIdentifier, string $fileName): void;

    protected function writeFile(string $fileName, string $content): void
    {
        GeneralUtility::writeFile($fileName, $content);
    }

    protected function removeFile(string $fileName): void
    {
        $removeService = GeneralUtility::makeInstance(RemoveService::class);
        $removeService->file($fileName);
    }

    protected function renderTemplateToFile(string $templateName, array $variables, string $htaccessFile): void
    {
        /** @var StandaloneView $renderer */
        $renderer = GeneralUtility::makeInstance(StandaloneView::class);
        $renderer->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName($templateName));
        $renderer->assignMultiple($variables);
        $content = trim((string) $renderer->render());
        // Note: Create even empty htaccess files (do not check!!!), so the delete is in sync
        $this->writeFile($htaccessFile, $content);
    }
}

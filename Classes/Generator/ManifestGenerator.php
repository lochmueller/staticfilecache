<?php
/**
 * ManifestGenerator.
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Generator;

use Psr\Http\Message\ResponseInterface;

// use SFC\Staticfilecache\Service\ManifestService;
// use SFC\Staticfilecache\Service\RemoveService;
// use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * ManifestGenerator.
 *
 * @todo implement
 */
class ManifestGenerator extends AbstractGenerator
{
    /**
     * Generate file.
     */
    public function generate(string $entryIdentifier, string $fileName, ResponseInterface $response, int $lifetime): void
    {
        // $manifestService = GeneralUtility::makeInstance(ManifestService::class);
        // $html = (string) $response->getBody();
        // $content = $manifestService->generateManifestContent($entryIdentifier, $html);
        // if ('' !== $content) {
        // GeneralUtility::writeFile($fileName.'.sfc', $content);
        // }
    }

    /**
     * Remove file.
     */
    public function remove(string $entryIdentifier, string $fileName): void
    {
        // $removeService = GeneralUtility::makeInstance(RemoveService::class);
        // $removeService->file($fileName.'.sfc');
    }
}

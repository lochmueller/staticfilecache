<?php

/**
 * GeneratorService.
 */
declare(strict_types = 1);

namespace SFC\Staticfilecache\Service;

use Psr\Http\Message\ResponseInterface;
use SFC\Staticfilecache\Generator\AbstractGenerator;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * GeneratorService.
 */
class GeneratorService extends AbstractService
{
    /**
     * Generate file.
     *
     * @param string $entryIdentifier
     * @param string $fileName
     * @param ResponseInterface $response
     * @param int $lifetime
     */
    public function generate(string $entryIdentifier, string $fileName, ResponseInterface &$response, int $lifetime): void
    {
        foreach (GeneralUtility::makeInstance(ObjectFactoryService::class)->get('Generator') as $implementation) {
            /* @var $implementation AbstractGenerator */
            $implementation->generate($entryIdentifier, $fileName, $response, $lifetime);
        }
    }

    /**
     * Remove file.
     *
     * @param string $entryIdentifier
     * @param string $fileName
     */
    public function remove(string $entryIdentifier, string $fileName): void
    {
        foreach (GeneralUtility::makeInstance(ObjectFactoryService::class)->get('Generator') as $implementation) {
            /* @var $implementation AbstractGenerator */
            $implementation->remove($entryIdentifier, $fileName);
        }
    }
}

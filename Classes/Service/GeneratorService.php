<?php

/**
 * GeneratorService.
 */
declare(strict_types = 1);

namespace SFC\Staticfilecache\Service;

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
     * @param string $data
     * @param int $lifetime
     */
    public function generate(string $entryIdentifier, string $fileName, string &$data, int $lifetime): void
    {
        foreach ($this->getImplementationObjects() as $implementation) {
            /* @var $implementation AbstractGenerator */
            $implementation->generate($entryIdentifier, $fileName, $data, $lifetime);
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
        foreach ($this->getImplementationObjects() as $implementation) {
            /* @var $implementation AbstractGenerator */
            $implementation->remove($entryIdentifier, $fileName);
        }
    }

    /**
     * Get implementation objects.
     *
     * @return array
     */
    protected function getImplementationObjects(): array
    {
        return GeneralUtility::makeInstance(ObjectFactoryService::class)->get('Generator');
    }
}

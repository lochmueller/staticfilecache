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
     */
    public function generate(string $entryIdentifier, string $fileName, string &$data): void
    {
        foreach ($this->getImplementationObjects() as $implementation) {
            /* @var $implementation AbstractGenerator */
            $implementation->generate($entryIdentifier, $fileName, $data);
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
        $objects = [];
        foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['staticfilecache']['Generator'] ?? [] as $implementation) {
            $objects[] = GeneralUtility::makeInstance($implementation);
        }

        return $objects;
    }
}

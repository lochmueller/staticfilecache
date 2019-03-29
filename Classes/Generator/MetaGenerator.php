<?php

/**
 * Generator.
 */
declare(strict_types = 1);

namespace SFC\Staticfilecache\Generator;

use SFC\Staticfilecache\Service\ConfigurationService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

/**
 * Generator.
 */
class MetaGenerator extends AbstractGenerator
{
    /**
     * Generate file.
     *
     * @param string $entryIdentifier
     * @param string $fileName
     * @param string $data
     */
    public function generate(string $entryIdentifier, string $fileName, string $data)
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
    public function remove(string $entryIdentifier, string $fileName)
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
        foreach ($this->getImplementations() as $implementation) {
            $objects[] = GeneralUtility::makeInstance($implementation);
        }

        return $objects;
    }

    /**
     * Get implementations.
     *
     * @return array
     */
    protected function getImplementations(): array
    {
        $generators = [];
        $configurationService = GeneralUtility::makeInstance(ConfigurationService::class);

        if (!$configurationService->isBool('disablePlainGenerator')) {
            $generators[] = PlainGenerator::class;
        }
        if ($configurationService->isBool('enableGzipGenerator')) {
            $generators[] = GzipGenerator::class;
        }
        if ($configurationService->isBool('enableBrotliGenerator')) {
            $generators[] = BrotliGenerator::class;
        }

        $signalSlotDispatcher = GeneralUtility::makeInstance(Dispatcher::class);
        $params = [
            'generators' => $generators,
        ];
        try {
            $params = $signalSlotDispatcher->dispatch(__CLASS__, 'getImplementations', $params);
        } catch (\Exception $ex) {
        }

        return $params['generators'];
    }
}

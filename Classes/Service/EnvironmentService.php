<?php

/**
 * EnvironmentService
 */
namespace SFC\Staticfilecache\Service;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * EnvironmentService
 */
class EnvironmentService
{

    /**
     * Get information
     *
     * @return array
     */
    public function get(): array
    {
        $configurationService = GeneralUtility::makeInstance(ConfigurationService::class);

        return array_merge([
            'TYPO3 Version' => \TYPO3_VERSION,
            'SFC Version' => ExtensionManagementUtility::getExtensionVersion('staticfilecache'),
            'PHP Version' => phpversion(),
            'OS' => Environment::isWindows() ? 'Windows' : 'Unix',
            'Composer' => Environment::isComposerMode() ? 'yes' : 'no',
            'SFC Settings' => '(see below)',
        ], $configurationService->getAll());
    }

    /**
     * Get markdown
     *
     * @return string
     */
    public function getMarkdown(): string
    {
        $result = ['**Environment**'];
        foreach ($this->get() as $key => $value) {
            $result[] = '* **' . $key . '**: ' . $value;
        }
        return implode("\n", $result);
    }

    /**
     * Get Link
     *
     * @return string
     */
    public function getLink(): string
    {
        return rawurlencode($this->getMarkdown());
    }
}

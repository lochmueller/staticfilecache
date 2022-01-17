<?php

declare(strict_types=1);

/**
 * EnvironmentService.
 */

namespace SFC\Staticfilecache\Service;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

/**
 * EnvironmentService.
 */
class EnvironmentService
{
    /**
     * Get information.
     */
    public function get(): array
    {
        $configurationService = GeneralUtility::makeInstance(ConfigurationService::class);

        return array_merge([
            'TYPO3 Version' => VersionNumberUtility::getCurrentTypo3Version(),
            'SFC Version' => ExtensionManagementUtility::getExtensionVersion('staticfilecache'),
            'PHP Version' => PHP_VERSION,
            'OS' => Environment::isWindows() ? 'Windows' : 'Unix',
            'Composer' => Environment::isComposerMode() ? 'yes' : 'no',
            'SFC Settings' => '(see below)',
        ], $configurationService->getAll());
    }

    /**
     * Get markdown.
     */
    public function getMarkdown(): string
    {
        $result = ['... Add your description here ...', '', '', '**Environment**'];
        foreach ($this->get() as $key => $value) {
            $result[] = '* **'.$key.'**: '.$value;
        }

        return implode("\n", $result);
    }

    /**
     * Get Link.
     */
    public function getLink(): string
    {
        return rawurlencode($this->getMarkdown());
    }
}

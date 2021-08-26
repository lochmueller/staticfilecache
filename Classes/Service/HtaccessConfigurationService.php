<?php

/**
 * HtaccessConfigurationService.
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Service;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * HtaccessConfigurationService.
 */
class HtaccessConfigurationService extends AbstractService
{
    /**
     * Check if the SFC_FULLPATH string is found in htaccess file.
     */
    public function foundConfigurationInHtaccess(): bool
    {
        foreach ($this->getHtaccessPaths() as $path) {
            if (is_file($path)) {
                $content = GeneralUtility::getUrl($path);
                if ((bool) strpos($content, 'SFC_FULLPATH')) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get all relevant htaccess paths.
     *
     * @todo check if we add another path, if typo3-secure-web is installed?!
     */
    public function getHtaccessPaths(): array
    {
        return [
            Environment::getPublicPath().'/.htaccess',
        ];
    }

    /**
     * get if the apache support the needed modules.
     */
    public function getMissingApacheModules(): array
    {
        if (!\function_exists('apache_get_modules')) {
            return [];
        }
        $required = [
            'mod_rewrite',
            'mod_headers',
            'mod_expires',
        ];

        return array_diff($required, apache_get_modules());
    }
}

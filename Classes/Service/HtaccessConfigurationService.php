<?php

/**
 * HtaccessConfigurationService
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Service;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * HtaccessConfigurationService
 */
class HtaccessConfigurationService extends AbstractService
{

    /**
     * Check if the SFC_FULLPATH string is found in htaccess file
     *
     * @return bool
     */
    public function foundConfigurationInHtaccess(): bool
    {
        $htacessFile = Environment::getPublicPath() . '/.htaccess';
        if (!is_file($htacessFile)) {
            return false;
        }

        $content = GeneralUtility::getUrl($htacessFile);

        return (bool)strpos($content, 'SFC_FULLPATH');
    }

    /**
     * get if the apache support the needed modules
     *
     * @return array
     */
    public function getMissingApacheModules(): array
    {
        $modules = function_exists('apache_get_modules') ? \apache_get_modules() : [];
        $required = [
            'mod_rewrite',
            'mod_headers',
            'mod_expires',
        ];
        return array_diff($required, $modules);
    }
}

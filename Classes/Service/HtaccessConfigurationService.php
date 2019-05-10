<?php

/**
 * HtaccessConfigurationService
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Service;

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
        $htacessFile = GeneralUtility::getFileAbsFileName('.htaccess');
        if (!is_file($htacessFile)) {
            return false;
        }

        $content = GeneralUtility::getUrl($htacessFile);

        return (bool)strpos($content, 'SFC_FULLPATH');
    }
}

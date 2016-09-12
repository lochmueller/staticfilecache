<?php
/**
 * Check Composer dependencies
 *
 * @author  Tim Lochmüller
 */

namespace SFC\Staticfilecache\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Check Composer dependencies
 */
class ComposerUtility
{

    /**
     * Check if composer is installed
     */
    static public function check()
    {
        if (class_exists(\GuzzleHttp\Client::class)) {
            return;
        }
        $path = GeneralUtility::getFileAbsFileName('EXT:staticfilecache/Resources/Private/Contrib/vendor/autoload.php');
        GeneralUtility::requireFile($path);
    }

}

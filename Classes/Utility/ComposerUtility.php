<?php
/**
 * Check Composer dependencies
 *
 * @author  Tim Lochmüller
 */

namespace SFC\Staticfilecache\Utility;

use GuzzleHttp\Client;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Check Composer dependencies
 */
class ComposerUtility
{

    /**
     * Check if composer is installed
     */
    public static function check()
    {
        if (class_exists(Client::class)) {
            return;
        }
        $path = GeneralUtility::getFileAbsFileName('EXT:staticfilecache/Resources/Private/Contrib/vendor/autoload.php');
        GeneralUtility::requireFile($path);
    }
}

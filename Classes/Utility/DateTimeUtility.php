<?php
/**
 * DateTimeUtility
 *
 * @author  Tim Lochmüller
 */

namespace SFC\Staticfilecache\Utility;

use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * DateTimeUtility
 *
 */
class DateTimeUtility
{

    /**
     * Get current time (respect EXEC_TIME)
     * Same time for the complete request
     *
     * @return int
     */
    public static function getCurrentTime()
    {
        static $time = 0;
        if ($time !== 0) {
            return $time;
        }
        if (isset($GLOBALS['EXEC_TIME']) && MathUtility::canBeInterpretedAsInteger($GLOBALS['EXEC_TIME'])) {
            $time = (int)$GLOBALS['EXEC_TIME'];
        } else {
            $time = time();
        }
        return $time;
    }

}

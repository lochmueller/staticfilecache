<?php


namespace SFC\Staticfilecache\Service;

use TYPO3\CMS\Core\Utility\MathUtility;


/**
 * DateTimeService
 */
class DateTimeService extends AbstractService
{
    /**
     * Get current time (respect EXEC_TIME)
     * Same time for the complete request.
     *
     * @return int
     */
    public function getCurrentTime()
    {
        static $time = 0;
        if (0 !== $time) {
            return $time;
        }
        $time = \time();
        if (isset($GLOBALS['EXEC_TIME']) && MathUtility::canBeInterpretedAsInteger($GLOBALS['EXEC_TIME'])) {
            $time = (int) $GLOBALS['EXEC_TIME'];
        }

        return $time;
    }
}
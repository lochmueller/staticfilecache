<?php

/**
 * DateTimeService.
 */
declare(strict_types = 1);

namespace SFC\Staticfilecache\Service;

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * DateTimeService.
 */
class DateTimeService extends AbstractService
{
    /**
     * Get current time (respect EXEC_TIME)
     * Same time for the complete request.
     *
     * @return int
     */
    public function getCurrentTime(): int
    {
        static $time = 0;
        if (0 !== $time) {
            return $time;
        }
        $time = (int)GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('date', 'timestamp');
        return $time;
    }
}

<?php

/**
 * DateTimeService.
 */
declare(strict_types=1);

namespace SFC\Staticfilecache\Service;

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * DateTimeService.
 */
class DateTimeService extends AbstractService
{
    /**
     * Get current time
     * Same time for the complete request.
     */
    public function getCurrentTime(): int
    {
        static $time = 0;
        if (0 !== $time) {
            return $time;
        }

        try {
            $time = (int) GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('date', 'timestamp');
        } catch (\Exception $exception) {
            $time = time();
        }

        return $time;
    }
}

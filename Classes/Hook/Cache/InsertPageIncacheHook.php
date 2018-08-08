<?php

/**
 * InsertPageIncacheHook.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Hook\Cache;

use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * InsertPageIncacheHook.
 */
class InsertPageIncacheHook extends AbstractCacheHook
{
    /**
     * Insert cache entry.
     *
     * @param TypoScriptFrontendController $tsfe        The parent object
     * @param int                          $timeOutTime The timestamp when the page times out
     */
    public function insertPageInCache(TypoScriptFrontendController $tsfe, $timeOutTime)
    {
        $this->getStaticFileCache()->insertPageInCache($tsfe, (int)$timeOutTime);
    }
}

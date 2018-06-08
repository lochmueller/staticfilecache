<?php

/**
 * InsertPageIncache.
 */
declare(strict_types=1);

namespace SFC\Staticfilecache\Hook\Cache;

use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * InsertPageIncache.
 */
class InsertPageIncache extends AbstractCacheHook
{
    /**
     * Check if the SFC should create the cache.
     *
     * @param TypoScriptFrontendController $tsfe        The parent object
     * @param int                          $timeOutTime The timestamp when the page times out
     *
     * @throws \TYPO3\CMS\Core\Cache\Exception\InvalidDataException
     */
    public function insertPageInCache(TypoScriptFrontendController $tsfe, $timeOutTime)
    {
        $this->getStaticFileCache()->insertPageInCache($tsfe, $timeOutTime);
    }
}

<?php

declare(strict_types=1);
/**
 * AbstractCacheHook.
 */

namespace SFC\Staticfilecache\Hook\Cache;

use SFC\Staticfilecache\Hook\AbstractHook;
use SFC\Staticfilecache\StaticFileCache;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * AbstractCacheHook.
 */
abstract class AbstractCacheHook extends AbstractHook
{
    /**
     * Get static file cache object.
     *
     * @return StaticFileCache|object
     */
    protected function getStaticFileCache()
    {
        return GeneralUtility::makeInstance(StaticFileCache::class);
    }
}

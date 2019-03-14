<?php

/**
 * AbstractCacheHook.
 */

declare(strict_types = 1);

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
     * Get StaticFileCache object.
     *
     * @return StaticFileCache
     */
    protected function getStaticFileCache(): StaticFileCache
    {
        return GeneralUtility::makeInstance(StaticFileCache::class);
    }
}

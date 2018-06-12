<?php
/**
 * Log no cache.
 */
declare(strict_types = 1);

namespace SFC\Staticfilecache\Hook;

use SFC\Staticfilecache\StaticFileCache;

/**
 * Log no cache.
 */
class LogNoCache extends AbstractHook
{
    /**
     * Log cache miss if no_cache is true.
     *
     * @param array  $parameters
     * @param object $parentObject
     *
     * @throws \TYPO3\CMS\Core\Cache\Exception\InvalidDataException
     */
    public function log(&$parameters, $parentObject)
    {
        if ($parameters['pObj']) {
            if ($parameters['pObj']->no_cache) {
                $timeOutTime = 0;
                StaticFileCache::getInstance()
                    ->insertPageInCache($parameters['pObj'], $timeOutTime);
            }
        }
    }
}

<?php
/**
 * Log no cache
 *
 * @author         Tim Lochmüller
 * @author         Daniel Poetzinger
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Hook;

use SFC\Staticfilecache\StaticFileCache;

/**
 * Log no cache
 */
class LogNoCache extends AbstractHook
{

    /**
     * Log cache miss if no_cache is true
     *
     * @param    array $params : Parameters delivered by the calling object
     * @param    object $parent : The calling parent object
     *
     * @return    void
     */
    public function log(&$params, $parent)
    {
        if ($params['pObj']) {
            if ($params['pObj']->no_cache) {
                $timeOutTime = 0;
                StaticFileCache::getInstance()
                    ->insertPageInCache($params['pObj'], $timeOutTime);
            }
        }
    }
}

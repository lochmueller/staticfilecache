<?php

/**
 * Caching Trait.
 */
declare(strict_types=1);

namespace SFC\Staticfilecache\Traits;

use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\AbstractFrontend;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Cache Trait.
 *
 * Usage:
 *
 * $result = $this->cacheRunTime('myCacheKey', function () use ($test, $test2) {
 *      // Do complex stuff and use $this and $test, $test2
 *      return 'Result';
 * });
 *
 * $result = $this->cacheLongTime('myCacheKey2', function () use ($test, $test2) {
 *      // Do complex stuff and use $this and $test, $test2
 *      return 'Result';
 * });
 *
 * $result = $this->cacheRemoteUri('https://www.google.de/images/branding/googlelogo/2x/googlelogo_color_272x92dp.png');
 */
trait CacheTrait
{
    /**
     * Cache run time.
     */
    protected function cacheRunTime(string $entryIdentifier, callable $callback)
    {
        return $this->cacheViaTrait($entryIdentifier, $callback, 'runtime');
    }

    /**
     * Cache long time.
     *
     * @param int $lifetime Default 60 Minutes (3.600 seconds)
     */
    protected function cacheLongTime(string $entryIdentifier, callable $callback, int $lifetime = 3600, array $tags = [])
    {
        return $this->cacheViaTrait('sfc_'.$entryIdentifier, $callback, 'pagesection', $lifetime, $tags);
    }

    /**
     * Cache remote file time.
     *
     * @param int $lifetime Default 60 Minutes (3.600 seconds)
     */
    protected function cacheRemoteUri(string $entryIdentifier, int $lifetime = 3600, array $tags = [])
    {
        $result = $this->cacheViaTrait($entryIdentifier, function (): void {
        }, 'remote_file', $lifetime, $tags);
        if (null === $result) {
            // call two times, because the anonym function is not the real result.
            // The result is output by the get method of the remote_file backend.
            $result = $this->cacheViaTrait($entryIdentifier, function (): void {
            }, 'remote_file', $lifetime, $tags);
        }

        return $result;
    }

    /**
     * Cache via Trait logic.
     */
    protected function cacheViaTrait(string $entryIdentifier, callable $callback, string $cacheIdentifier, int $lifetime = 0, array $tags = [])
    {
        try {
            /** @var AbstractFrontend $cache */
            $cache = GeneralUtility::makeInstance(CacheManager::class)->getCache($cacheIdentifier);
        } catch (\Exception $exception) {
            $logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
            $logger->error($exception->getMessage(), ['entryIdentifier' => $entryIdentifier, 'cacheIdentifier' => $cacheIdentifier]);

            return \call_user_func_array($callback, []);
        }

        // Do not use "has" because the cache is a shared resource
        $result = $cache->get($entryIdentifier);
        if (false !== $result) {
            return $result;
        }
        $result = \call_user_func_array($callback, []);
        $cache->set($entryIdentifier, $result, $tags, 0 === $lifetime ? null : $lifetime);

        return $result;
    }
}

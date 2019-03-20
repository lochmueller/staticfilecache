<?php

/**
 * Cache backend for StaticFileCache.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Cache;

use SFC\Staticfilecache\Domain\Repository\CacheRepository;
use SFC\Staticfilecache\Service\DateTimeService;
use SFC\Staticfilecache\Service\HtaccessService;
use SFC\Staticfilecache\Service\QueueService;
use SFC\Staticfilecache\Service\RemoveService;
use TYPO3\CMS\Core\Cache\Backend\TransientBackendInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

/**
 * Cache backend for StaticFileCache.
 *
 * This cache handle the file representation of the cache and handle
 * - CacheFileName
 * - CacheFileName.gz
 */
class StaticFileBackend extends StaticDatabaseBackend implements TransientBackendInterface
{
    /**
     * Cache directory.
     *
     * @var string
     */
    const CACHE_DIRECTORY = 'typo3temp/tx_staticfilecache/';

    /**
     * Saves data in the cache.
     *
     * @param string $entryIdentifier An identifier for this specific cache entry
     * @param string $data            The data to be stored
     * @param array  $tags            Tags to associate with this cache entry
     * @param int    $lifetime        Lifetime of this cache entry in seconds
     *
     * @throws \TYPO3\CMS\Core\Cache\Exception                      if no cache frontend has been set
     * @throws \TYPO3\CMS\Core\Cache\Exception\InvalidDataException if the data is not a string
     */
    public function set($entryIdentifier, $data, array $tags = [], $lifetime = null)
    {
        $realLifetime = $this->getRealLifetime($lifetime);
        $time = (new DateTimeService())->getCurrentTime();
        $databaseData = [
            'created' => $time,
            'expires' => ($time + $realLifetime),
        ];
        if (\in_array('explanation', $tags, true)) {
            $databaseData['explanation'] = $data;
            parent::set($entryIdentifier, \serialize($databaseData), $tags, $realLifetime);

            return;
        }

        $this->logger->debug('SFC Set', [$entryIdentifier, $tags, $lifetime]);

        // call set in front of the generation, because the set method
        // of the DB backend also call remove
        parent::set($entryIdentifier, \serialize($databaseData), $tags, $realLifetime);

        $fileName = $this->getCacheFilename($entryIdentifier);
        $cacheDir = (string)PathUtility::pathinfo($fileName, PATHINFO_DIRNAME);
        if (!\is_dir($cacheDir)) {
            GeneralUtility::mkdir_deep($cacheDir);
        }

        $this->removeStaticFiles($entryIdentifier);

        // normal
        GeneralUtility::writeFile($fileName, $data);

        if ($this->configuration->isBool('enableStaticFileCompression')) {
            $dispatchArguments = [
                'fileName' => $fileName,
                'data' => $data,
            ];
            $this->dispatch('compress', $dispatchArguments);
        }

        GeneralUtility::makeInstance(HtaccessService::class)->write($fileName, $realLifetime);
    }

    /**
     * Loads data from the cache (DB).
     *
     * @param string $entryIdentifier An identifier which describes the cache entry to load
     *
     * @return mixed The cache entry's content as a string or FALSE if the cache entry could not be loaded
     */
    public function get($entryIdentifier)
    {
        if (!$this->has($entryIdentifier)) {
            return false;
        }
        $result = parent::get($entryIdentifier);
        if (!\is_string($result)) {
            return false;
        }

        return \unserialize($result);
    }

    /**
     * Checks if a cache entry with the specified identifier exists.
     *
     * @param string $entryIdentifier An identifier specifying the cache entry
     *
     * @return bool TRUE if such an entry exists, FALSE if not
     */
    public function has($entryIdentifier)
    {
        return \is_file($this->getCacheFilename($entryIdentifier)) || parent::has($entryIdentifier);
    }

    /**
     * Removes all cache entries matching the specified identifier.
     * Usually this only affects one entry but if - for what reason ever -
     * old entries for the identifier still exist, they are removed as well.
     *
     * @param string $entryIdentifier Specifies the cache entry to remove
     *
     * @return bool TRUE if (at least) an entry could be removed or FALSE if no entry was found
     */
    public function remove($entryIdentifier)
    {
        if (!$this->has($entryIdentifier)) {
            return false;
        }

        $this->logger->debug('SFC Remove', [$entryIdentifier]);

        if ($this->isBoostMode()) {
            $this->getQueue()
                ->addIdentifier($entryIdentifier);

            return true;
        }

        if ($this->removeStaticFiles($entryIdentifier)) {
            return parent::remove($entryIdentifier);
        }

        return false;
    }

    /**
     * Removes all cache entries of this cache.
     *
     * @throws \TYPO3\CMS\Core\Cache\Exception
     */
    public function flush()
    {
        if (false === (bool)$this->configuration->get('clearCacheForAllDomains')) {
            $this->flushByTag('sfc_domain_' . \str_replace('.', '_', GeneralUtility::getIndpEnv('TYPO3_HOST_ONLY')));

            return;
        }

        $this->logger->debug('SFC Flush');

        if ($this->isBoostMode()) {
            $identifiers = GeneralUtility::makeInstance(CacheRepository::class)->findAllIdentifiers();
            $this->getQueue()->addIdentifiers($identifiers);

            return;
        }

        $absoluteCacheDir = GeneralUtility::getFileAbsFileName(self::CACHE_DIRECTORY);
        $removeService = GeneralUtility::makeInstance(RemoveService::class);
        $removeService->softRemoveDir($absoluteCacheDir . 'https/');
        $removeService->softRemoveDir($absoluteCacheDir . 'http/');
        parent::flush();
        $removeService->removeDirs();
    }

    /**
     * Removes all entries tagged by any of the specified tags.
     *
     * @param string[] $tags
     *
     * @throws \TYPO3\CMS\Core\Cache\Exception
     */
    public function flushByTags(array $tags)
    {
        $this->throwExceptionIfFrontendDoesNotExist();

        if (empty($tags)) {
            return;
        }

        $this->logger->debug('SFC flushByTags', [$tags]);

        $identifiers = [];
        foreach ($tags as $tag) {
            $identifiers = \array_merge($identifiers, $this->findIdentifiersByTagIncludingExpired($tag));
        }

        if ($this->isBoostMode()) {
            $this->getQueue()->addIdentifiers($identifiers);

            return;
        }

        foreach ($identifiers as $identifier) {
            $this->removeStaticFiles($identifier);
        }

        parent::flushByTags($tags);
    }

    /**
     * Removes all cache entries of this cache which are tagged by the specified tag.
     *
     * @param string $tag The tag the entries must have
     *
     * @throws \TYPO3\CMS\Core\Cache\Exception
     */
    public function flushByTag($tag)
    {
        $this->throwExceptionIfFrontendDoesNotExist();

        $this->logger->debug('SFC flushByTags', [$tag]);
        $identifiers = $this->findIdentifiersByTagIncludingExpired($tag);

        if ($this->isBoostMode()) {
            $this->getQueue()->addIdentifiers($identifiers);

            return;
        }

        foreach ($identifiers as $identifier) {
            $this->removeStaticFiles($identifier);
        }
        parent::flushByTag($tag);
    }

    /**
     * Does garbage collection.
     */
    public function collectGarbage()
    {
        $expiredIdentifiers = GeneralUtility::makeInstance(CacheRepository::class)->findExpiredIdentifiers();
        if ($this->isBoostMode()) {
            $this->getQueue()->addIdentifiers($expiredIdentifiers);

            return;
        }
        parent::collectGarbage();
        foreach ($expiredIdentifiers as $identifier) {
            $this->removeStaticFiles($identifier);
        }
    }

    /**
     * Get the cache folder for the given entry.
     *
     * @param $entryIdentifier
     *
     * @return string
     */
    protected function getCacheFilename(string $entryIdentifier): string
    {
        $urlParts = \parse_url($entryIdentifier);
        $parts = [
            $urlParts['scheme'],
            $urlParts['host'],
        ];

        // Add by configuration?
        // $parts[] = isset($urlParts['port']) ? (int)$urlParts['port'] : 80;

        $path = \implode('/', $parts) . '/' . \trim($urlParts['path'], '/');
        $cacheFilename = GeneralUtility::getFileAbsFileName(self::CACHE_DIRECTORY . $path);
        $fileExtension = (string)PathUtility::pathinfo(PathUtility::basename($cacheFilename), PATHINFO_EXTENSION);
        if (empty($fileExtension) || !GeneralUtility::inList($this->configuration->get('fileTypes'), $fileExtension)) {
            $cacheFilename = \rtrim($cacheFilename, '/') . '/index.html';
        }

        return $cacheFilename;
    }

    /**
     * Call findIdentifiersByTag but ignore the expires check.
     *
     * @param string $tag
     *
     * @return array
     */
    protected function findIdentifiersByTagIncludingExpired($tag): array
    {
        $base = (new DateTimeService())->getCurrentTime();
        $GLOBALS['EXEC_TIME'] = 0;
        $identifiers = $this->findIdentifiersByTag($tag);
        $GLOBALS['EXEC_TIME'] = $base;

        return $identifiers;
    }

    /**
     * Remove the static files of the given identifier.
     *
     * @param string $entryIdentifier
     *
     * @return bool success if the files are deleted
     */
    protected function removeStaticFiles(string $entryIdentifier): bool
    {
        $fileName = $this->getCacheFilename($entryIdentifier);
        $dispatchArguments = [
            'entryIdentifier' => $entryIdentifier,
            'fileName' => $fileName,
            'files' => [
                $fileName,
                PathUtility::pathinfo($fileName, PATHINFO_DIRNAME) . '/.htaccess',
            ],
        ];

        if ($this->configuration->isBool('enableStaticFileCompression')) {
            $dispatchArguments['files'][] = $fileName . '.gz';
        }

        $dispatched = $this->dispatch('removeStaticFiles', $dispatchArguments);
        $files = $dispatched['files'];
        $removeService = GeneralUtility::makeInstance(RemoveService::class);
        foreach ($files as $file) {
            if (false === $removeService->removeFile($file)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get queue manager.
     *
     * @return QueueService
     */
    protected function getQueue(): QueueService
    {
        return GeneralUtility::makeInstance(QueueService::class);
    }

    /**
     * Check if boost mode is active and if the calls are not part of the worker.
     *
     * @return bool
     */
    protected function isBoostMode(): bool
    {
        return (bool)$this->configuration->get('boostMode') && !\defined('SFC_QUEUE_WORKER');
    }
}

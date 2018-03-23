<?php
/**
 * Cache backend for static file cache.
 */
declare(strict_types=1);

namespace SFC\Staticfilecache\Cache;

use SFC\Staticfilecache\Service\HtaccessService;
use SFC\Staticfilecache\Service\QueueService;
use SFC\Staticfilecache\Utility\DateTimeUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

/**
 * Cache backend for static file cache.
 *
 * This cache handle the file representation of the cache and handle
 * - CacheFileName
 * - CacheFileName.gz
 */
class StaticFileBackend extends AbstractBackend
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
        $databaseData = [
            'created' => DateTimeUtility::getCurrentTime(),
            'expires' => (DateTimeUtility::getCurrentTime() + $this->getRealLifetime($lifetime)),
        ];
        if (\in_array('explanation', $tags, true)) {
            $databaseData['explanation'] = $data;
            parent::set($entryIdentifier, \serialize($databaseData), $tags, $lifetime);

            return;
        }

        // call set in front of the generation, because the set method
        // of the DB backend also call remove
        parent::set($entryIdentifier, \serialize($databaseData), $tags, $lifetime);

        $fileName = $this->getCacheFilename($entryIdentifier);
        $cacheDir = PathUtility::pathinfo($fileName, PATHINFO_DIRNAME);
        if (!\is_dir($cacheDir)) {
            GeneralUtility::mkdir_deep($cacheDir);
        }

        $this->removeStaticFiles($entryIdentifier);

        // normal
        GeneralUtility::writeFile($fileName, $data);

        // gz
        if ($this->configuration->isBool('enableStaticFileCompression')) {
            $contentGzip = \gzencode($data, $this->getCompressionLevel());
            if ($contentGzip) {
                GeneralUtility::writeFile($fileName . '.gz', $contentGzip);
            }
        }

        GeneralUtility::makeInstance(HtaccessService::class)->write($fileName, $this->getRealLifetime($lifetime));
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
            return;
        }
        $result = parent::get($entryIdentifier);
        if (!\is_string($result)) {
            return;
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

        if ($this->isBoostMode()) {
            $this->getQueue()
                ->addIdentifier($entryIdentifier);

            return true;
        }

        $this->removeStaticFiles($entryIdentifier);

        return parent::remove($entryIdentifier);
    }

    /**
     * Removes all cache entries of this cache.
     */
    public function flush()
    {
        if (false === (bool) $this->configuration->get('clearCacheForAllDomains')) {
            $this->flushByTag('sfc_domain_' . \str_replace('.', '_', GeneralUtility::getIndpEnv('TYPO3_HOST_ONLY')));

            return;
        }

        if ($this->isBoostMode()) {
            $identifiers = $this->getExpiredIdentifiers();
            $queue = $this->getQueue();
            foreach ($identifiers as $identifier) {
                $queue->addIdentifier($identifier);
            }

            return;
        }

        $absoluteCacheDir = GeneralUtility::getFileAbsFileName(self::CACHE_DIRECTORY);
        if (\is_dir($absoluteCacheDir)) {
            $tempAbsoluteCacheDir = \rtrim($absoluteCacheDir, '/') . '_' . GeneralUtility::milliseconds() . '/';
            \rename($absoluteCacheDir, $tempAbsoluteCacheDir);
        }
        parent::flush();
        if (isset($tempAbsoluteCacheDir)) {
            GeneralUtility::rmdir($tempAbsoluteCacheDir, true);
        }
    }

    /**
     * Removes all entries tagged by any of the specified tags.
     *
     * @param string[] $tags
     */
    public function flushByTags(array $tags)
    {
        $this->throwExceptionIfFrontendDoesNotExist();

        if (empty($tags)) {
            return;
        }

        $this->removeStaticFilesByTags($tags);
        if (!$this->isBoostMode()) {
            parent::flushByTags($tags);
        }
    }

    /**
     * Removes all cache entries of this cache which are tagged by the specified tag.
     *
     * @param string $tag The tag the entries must have
     */
    public function flushByTag($tag)
    {
        $this->removeStaticFilesByTags([$tag]);
        if (!$this->isBoostMode()) {
            parent::flushByTag($tag);
        }
    }

    /**
     * Does garbage collection.
     */
    public function collectGarbage()
    {
        $expiredIdentifiers = $this->getExpiredIdentifiers();
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

        $path = implode('/', $parts) . '/' . \trim($urlParts['path'], '/');
        $cacheFilename = GeneralUtility::getFileAbsFileName(self::CACHE_DIRECTORY . $path);
        $fileExtension = PathUtility::pathinfo(PathUtility::basename($cacheFilename), PATHINFO_EXTENSION);
        if (empty($fileExtension) || !GeneralUtility::inList($this->configuration->get('fileTypes'), $fileExtension)) {
            $cacheFilename = \rtrim($cacheFilename, '/') . '/index.html';
        }

        return $cacheFilename;
    }

    /**
     * Remove the static files of the given tag entries or add it to the queue.
     *
     * @param $tags
     */
    protected function removeStaticFilesByTags($tags)
    {
        $identifiers = [];
        foreach ($tags as $tag) {
            $identifiers = \array_merge($identifiers, $this->findIdentifiersByTagIncludingExpired($tag));
        }

        if ($this->isBoostMode()) {
            $queue = $this->getQueue();
            foreach ($identifiers as $identifier) {
                $queue->addIdentifier($identifier);
            }

            return;
        }

        foreach ($identifiers as $identifier) {
            $this->removeStaticFiles($identifier);
        }
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
        $base = $GLOBALS['EXEC_TIME'];
        $GLOBALS['EXEC_TIME'] = 0;
        $identifiers = $this->findIdentifiersByTag($tag);
        $GLOBALS['EXEC_TIME'] = $base;

        return $identifiers;
    }

    /**
     * Remove the static files of the given identifier.
     *
     * @param string $entryIdentifier
     */
    protected function removeStaticFiles(string $entryIdentifier)
    {
        $fileName = $this->getCacheFilename($entryIdentifier);
        $files = [
            $fileName,
            $fileName . '.gz',
            PathUtility::pathinfo($fileName, PATHINFO_DIRNAME) . '/.htaccess',
        ];
        foreach ($files as $file) {
            if (\is_file($file)) {
                \unlink($file);
            }
        }
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
        return (bool) $this->configuration->get('boostMode') && !\defined('SFC_QUEUE_WORKER');
    }

    /**
     * Get the cache identifiers.
     *
     * @return array
     */
    protected function getExpiredIdentifiers(): array
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($this->cacheTable);
        $queryBuilder = $connection->createQueryBuilder();
        $rows = $queryBuilder->select('identifier')
            ->from($this->cacheTable)
            ->where($queryBuilder->expr()->lt(
                'expires',
                $queryBuilder->createNamedParameter($GLOBALS['EXEC_TIME'], \PDO::PARAM_INT)
            ))
            ->groupBy('identifier')
            ->execute()
            ->fetchAll();

        $cacheIdentifiers = [];
        foreach ($rows as $row) {
            $cacheIdentifiers[] = $row['identifier'];
        }

        return $cacheIdentifiers;
    }
}

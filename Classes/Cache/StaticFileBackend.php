<?php

/**
 * Cache backend for StaticFileCache.
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Cache;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use SFC\Staticfilecache\Domain\Repository\CacheRepository;
use SFC\Staticfilecache\Domain\Repository\QueueRepository;
use SFC\Staticfilecache\Service\CacheService;
use SFC\Staticfilecache\Service\ClientService;
use SFC\Staticfilecache\Service\ConfigurationService;
use SFC\Staticfilecache\Service\DateTimeService;
use SFC\Staticfilecache\Service\GeneratorService;
use SFC\Staticfilecache\Service\QueueService;
use SFC\Staticfilecache\Service\RemoveService;
use TYPO3\CMS\Core\Cache\Backend\TransientBackendInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

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
     * Saves data in the cache.
     *
     * @param string            $entryIdentifier An identifier for this specific cache entry
     * @param ResponseInterface $data            The data to be stored
     * @param array             $tags            Tags to associate with this cache entry
     * @param int               $lifetime        Lifetime of this cache entry in seconds
     *
     * @throws \TYPO3\CMS\Core\Cache\Exception                      if no cache frontend has been set
     * @throws \TYPO3\CMS\Core\Cache\Exception\InvalidDataException if the data is not a string
     */
    public function set($entryIdentifier, $data, array $tags = [], $lifetime = null): void
    {
        $realLifetime = $this->getRealLifetime($lifetime);
        $time = (new DateTimeService())->getCurrentTime();
        $databaseData = [
            'created' => $time,
            'expires' => ($time + $realLifetime),
            'priority' => $this->getPriority($entryIdentifier),
        ];
        if (\in_array('explanation', $tags, true)) {
            $databaseData['explanation'] = $data->getHeader('X-SFC-Explanation');
            if (!parent::has($entryIdentifier)) {
                // Add only the details if there is no valid cache entry
                parent::set($entryIdentifier, serialize($databaseData), $tags, $realLifetime);
            }

            return;
        }

        $this->logger->debug('SFC Set', [$entryIdentifier, $tags, $lifetime]);

        $identifierBuilder = GeneralUtility::makeInstance(IdentifierBuilder::class);
        $fileName = $identifierBuilder->getFilepath($entryIdentifier);

        try {
            // Create dir
            $cacheDir = (string) PathUtility::pathinfo($fileName, PATHINFO_DIRNAME);
            if (!is_dir($cacheDir)) {
                GeneralUtility::mkdir_deep($cacheDir);
            }

            if ($this->isHashedIdentifier()) {
                $databaseData['url'] = $entryIdentifier;
                $entryIdentifierForDatabase = $this->hash($entryIdentifier);
            } else {
                $entryIdentifierForDatabase = $entryIdentifier;
            }

            // call set in front of the generation, because the set method
            // of the DB backend also call remove (this remove do not remove the folder already created above)
            parent::set($entryIdentifierForDatabase, serialize($databaseData), $tags, $realLifetime);

            $this->removeStaticFiles($entryIdentifier);

            GeneralUtility::makeInstance(GeneratorService::class)->generate($entryIdentifier, $fileName, $data, $realLifetime);
        } catch (\Exception $exception) {
            $this->logger->error('Error in cache create process', ['exception' => $exception]);
        }
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

        return unserialize($result);
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
        return is_file($this->getFilepath($entryIdentifier)) || parent::has($entryIdentifier);
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
                ->addIdentifier($entryIdentifier)
            ;

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
    public function flush(): void
    {
        if (false === (bool) $this->configuration->get('clearCacheForAllDomains')) {
            $this->flushByTag('sfc_domain_'.str_replace('.', '_', GeneralUtility::getIndpEnv('TYPO3_HOST_ONLY')));

            return;
        }

        $this->logger->debug('SFC Flush');

        if ($this->isBoostMode()) {
            $identifiers = GeneralUtility::makeInstance(CacheRepository::class)->findAllIdentifiers();
            $this->getQueue()->addIdentifiers($identifiers);

            return;
        }

        $absoluteCacheDir = GeneralUtility::makeInstance(CacheService::class)->getAbsoluteBaseDirectory();
        $removeService = GeneralUtility::makeInstance(RemoveService::class);
        $removeService->subdirectories($absoluteCacheDir);
        parent::flush();
    }

    /**
     * Removes all entries tagged by any of the specified tags.
     *
     * @param string[] $tags
     *
     * @throws \TYPO3\CMS\Core\Cache\Exception
     */
    public function flushByTags(array $tags): void
    {
        $this->throwExceptionIfFrontendDoesNotExist();

        if (empty($tags)) {
            return;
        }

        $this->logger->debug('SFC flushByTags', [$tags]);

        $identifiers = [];
        foreach ($tags as $tag) {
            $identifiers = array_merge($identifiers, $this->findIdentifiersByTagIncludingExpired($tag));
        }

        if ($this->isBoostMode()) {
            $priority = QueueService::PRIORITY_LOW;

            if (1 === \count($tags) && str_starts_with($tags[0], 'pageId_')) {
                $priority = QueueService::PRIORITY_HIGH;
            }

            $this->getQueue()->addIdentifiers($identifiers, $priority);

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
    public function flushByTag($tag): void
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
     *
     * Note: Do not check boostmode. If we check boost mode, we get the problem, that the core garbage collection drop
     * the DB related part of StaticFileCache but not the files, because the "garbage collection" works directly on
     * the caching backends and not on the caches itself. By ignoring the boost mode in this function we take care,
     * that the StaticFileCache drop the file AND the db representation. Please take care, that you select both backends
     * in the garbage collection task in the Scheduler.
     */
    public function collectGarbage(): void
    {
        $expiredIdentifiers = GeneralUtility::makeInstance(CacheRepository::class)->findExpiredIdentifiers();
        parent::collectGarbage();
        foreach ($expiredIdentifiers as $identifier) {
            $this->removeStaticFiles($identifier);
        }
    }

    protected function hash(string $entryIdentifier): string
    {
        return hash('sha256', $entryIdentifier);
    }

    /**
     * Get prority.
     *
     * @return int
     */
    protected function getPriority(string $uri)
    {
        $priority = 0;
        $configuration = GeneralUtility::makeInstance(ConfigurationService::class);
        if ($configuration->isBool('useReverseUriLengthInPriority')) {
            $priority += (QueueService::PRIORITY_MEDIUM - \strlen($uri));
        }

        if (($GLOBALS['TSFE'] ?? null) instanceof TypoScriptFrontendController) {
            $priority += (int) $GLOBALS['TSFE']->page['tx_staticfilecache_cache_priority'];
        }

        return $priority;
    }

    /**
     * Get the cache folder for the given entry.
     *
     * @param $entryIdentifier
     */
    protected function getFilepath(string $entryIdentifier): string
    {
        $url = $entryIdentifier;
        if ($this->isHashedIdentifier()) {
            $data = parent::get($entryIdentifier);
            if (!$data) {
                return '';
            }
            $entry = unserialize($data);
            if (!empty($entry['url'])) {
                $url = $entry['url'];
            }
        }
        $identifierBuilder = GeneralUtility::makeInstance(IdentifierBuilder::class);

        return $identifierBuilder->getFilepath($url);
    }

    /**
     * Call findIdentifiersByTag but ignore the expires check.
     *
     * @param string $tag
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
     * @return bool success if the files are deleted
     */
    protected function removeStaticFiles(string $entryIdentifier): bool
    {
        $fileName = $this->getFilepath($entryIdentifier);
        GeneralUtility::makeInstance(GeneratorService::class)->remove($entryIdentifier, $fileName);

        return true;
    }

    /**
     * Get queue manager.
     */
    protected function getQueue(): QueueService
    {
        static $queueService;
        if (null === $queueService) {
            // Build Queue Service manually, because here is no DI
            $queueService = GeneralUtility::makeInstance(
                QueueService::class,
                GeneralUtility::makeInstance(QueueRepository::class),
                GeneralUtility::makeInstance(ConfigurationService::class),
                GeneralUtility::makeInstance(ClientService::class, GeneralUtility::makeInstance(EventDispatcherInterface::class)),
                GeneralUtility::makeInstance(CacheService::class)
            );
        }

        return $queueService;
    }

    /**
     * Check if boost mode is active (worker runs without boost mode).
     */
    protected function isBoostMode(): bool
    {
        $ignoreBoostQueue = (bool) getenv('STATIC_FILE_CACHE_IGNORE_BOOST_QUEUE');
        if ($ignoreBoostQueue) {
            return false;
        }

        return (bool) $this->configuration->get('boostMode');
    }

    /**
     * Check if the "hashUriInCache" feature is enabled.
     */
    protected function isHashedIdentifier(): bool
    {
        return (bool) $this->configuration->isBool('hashUriInCache');
    }
}

<?php
/**
 * RemoteFileBackend.
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Cache;

use SFC\Staticfilecache\Exception;
use SFC\Staticfilecache\Service\RemoveService;
use TYPO3\CMS\Core\Cache\Backend\AbstractBackend;
use TYPO3\CMS\Core\Cache\Backend\FreezableBackendInterface;
use TYPO3\CMS\Core\Cache\Backend\TaggableBackendInterface;
use TYPO3\CMS\Core\Cache\Backend\TransientBackendInterface;
use TYPO3\CMS\Core\Cache\Exception\InvalidDataException;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

/**
 * RemoteFileBackend.
 */
class RemoteFileBackend extends AbstractBackend implements TaggableBackendInterface, FreezableBackendInterface, TransientBackendInterface
{
    /**
     * Relative folder name.
     */
    public const RELATIVE_STORAGE_FOLDER = 'typo3temp/remote/';

    /**
     * File extension for tag files.
     */
    public const FILE_EXTENSION_TAG = '.cache.tags';

    /**
     * File extension for lifetime files.
     */
    public const FILE_EXTENSION_LIFETIME = '.cache.lifetime';

    /**
     * File extension for lifetime files.
     */
    public const FILE_EXTENSION_IDENTIFIER = '.cache.ident';

    /**
     * Is freezed?
     *
     * @var bool
     */
    protected $freeze = false;

    /**
     * Hash length.
     *
     * @var int
     */
    protected $hashLength = 3;

    /**
     * Set hash length.
     */
    public function setHashLength(int $hashLength): void
    {
        $this->hashLength = $hashLength;
    }

    /**
     * Saves data in the cache.
     *
     * @param string $entryIdentifier An identifier for this specific cache entry
     * @param string $data            The data to be stored
     * @param array  $tags            Tags to associate with this cache entry. If the backend does not support tags, this option can be ignored.
     * @param int    $lifetime        Lifetime of this cache entry in seconds. If NULL is specified, the default lifetime is used. "0" means unlimited lifetime.
     *
     * @throws \TYPO3\CMS\Core\Cache\Exception if no cache frontend has been set
     * @throws InvalidDataException            if the data is not a string
     * @throws \Exception                      If thee backend is frozen
     *
     * @api
     */
    public function set($entryIdentifier, $data, array $tags = [], $lifetime = null): void
    {
        if ($this->freeze) {
            throw new Exception('Backend is frozen!', 123789);
        }
        $this->remove($entryIdentifier);
        $fileName = $this->getFileName($entryIdentifier);

        if (\is_string($data) && '' !== $data) {
            $content = $data;
        } else {
            $content = GeneralUtility::getUrl($entryIdentifier);
            if (false === $content) {
                throw new InvalidDataException('Could not fetch URL: '.$entryIdentifier, 56757677);
            }
        }

        // Check cache dir
        $absoluteCacheDir = GeneralUtility::getFileAbsFileName(self::RELATIVE_STORAGE_FOLDER);
        if (!is_dir(PathUtility::dirname($absoluteCacheDir.$fileName))) {
            GeneralUtility::mkdir_deep(PathUtility::dirname($absoluteCacheDir.$fileName));
        }

        // create files
        if (false === GeneralUtility::writeFile($absoluteCacheDir.$fileName, $content, true)) {
            throw new InvalidDataException('Could not write local cache file', 7324892);
        }

        GeneralUtility::writeFile($absoluteCacheDir.$fileName.self::FILE_EXTENSION_TAG, '|'.implode('|', $tags).'|');
        GeneralUtility::writeFile($absoluteCacheDir.$fileName.self::FILE_EXTENSION_LIFETIME, $this->calculateExpiryTime($lifetime)->getTimestamp());
        GeneralUtility::writeFile($absoluteCacheDir.$fileName.self::FILE_EXTENSION_IDENTIFIER, $entryIdentifier);
    }

    /**
     * Loads data from the cache.
     *
     * @param string $entryIdentifier An identifier which describes the cache entry to load
     *
     * @return mixed The cache entry's content as a string or FALSE if the cache entry could not be loaded
     *
     * @api
     */
    public function get($entryIdentifier)
    {
        if (!$this->has($entryIdentifier)) {
            return false;
        }

        return self::RELATIVE_STORAGE_FOLDER.$this->getFileName($entryIdentifier);
    }

    /**
     * Checks if a cache entry with the specified identifier exists.
     *
     * @param string $entryIdentifier An identifier specifying the cache entry
     *
     * @return bool TRUE if such an entry exists, FALSE if not
     *
     * @api
     */
    public function has($entryIdentifier)
    {
        $folder = GeneralUtility::getFileAbsFileName(self::RELATIVE_STORAGE_FOLDER);
        $fileName = $this->getFileName($entryIdentifier);
        if (!is_file($folder.$fileName)) {
            return false;
        }
        if ($this->freeze) {
            return true;
        }
        $validUntil = (int) file_get_contents($folder.$fileName.self::FILE_EXTENSION_LIFETIME);

        return $validUntil > time();
    }

    /**
     * Removes all cache entries matching the specified identifier.
     * Usually this only affects one entry but if - for what reason ever -
     * old entries for the identifier still exist, they are removed as well.
     *
     * @param string $entryIdentifier Specifies the cache entry to remove
     *
     * @throws \Exception
     *
     * @return bool TRUE if (at least) an entry could be removed or FALSE if no entry was found
     *
     * @api
     */
    public function remove($entryIdentifier)
    {
        if ($this->freeze) {
            throw new Exception('Backend is frozen!', 123789);
        }
        $folder = GeneralUtility::getFileAbsFileName(self::RELATIVE_STORAGE_FOLDER);
        $fileName = $this->getFileName($entryIdentifier);
        if (!is_file($folder.$fileName)) {
            return false;
        }

        // Remove files
        $removeService = GeneralUtility::makeInstance(RemoveService::class);
        $removeService->file($folder.$fileName);
        $removeService->file($folder.$fileName.self::FILE_EXTENSION_TAG);
        $removeService->file($folder.$fileName.self::FILE_EXTENSION_LIFETIME);
        $removeService->file($folder.$fileName.self::FILE_EXTENSION_IDENTIFIER);

        return true;
    }

    /**
     * Removes all cache entries of this cache.
     *
     * @api
     */
    public function flush(): void
    {
        $removeService = GeneralUtility::makeInstance(RemoveService::class);
        $removeService->directory(GeneralUtility::getFileAbsFileName(self::RELATIVE_STORAGE_FOLDER));
        $this->freeze = false;
    }

    /**
     * Does garbage collection.
     *
     * @api
     *
     * @throws \Exception If the backend is frozen
     */
    public function collectGarbage(): void
    {
        if ($this->freeze) {
            throw new \Exception('Backend is frozen!', 123789);
        }

        $lifetimeFiles = glob(GeneralUtility::getFileAbsFileName(self::RELATIVE_STORAGE_FOLDER).'*/*/*'.self::FILE_EXTENSION_LIFETIME);

        $identifiers = [];

        foreach ($lifetimeFiles as $lifetimeFile) {
            if ((int) file_get_contents($lifetimeFile) < time()) {
                $identifiers[] = file_get_contents(str_replace(self::FILE_EXTENSION_LIFETIME, self::FILE_EXTENSION_IDENTIFIER, $lifetimeFile));
            }
        }

        foreach ($identifiers as $identifier) {
            $this->remove($identifier);
        }
    }

    /**
     * Removes all cache entries of this cache which are tagged by the specified tag.
     *
     * @param string $tag The tag the entries must have
     *
     * @api
     *
     * @throws \Exception
     */
    public function flushByTag($tag): void
    {
        if ($this->freeze) {
            throw new \Exception('Backend is frozen!', 123789);
        }
        $identifiers = $this->findIdentifiersByTag($tag);
        foreach ($identifiers as $identifier) {
            $this->remove($identifier);
        }
    }

    /**
     * Finds and returns all cache entry identifiers which are tagged by the
     * specified tag.
     *
     * @param string $tag The tag to search for
     *
     * @return array An array with identifiers of all matching entries. An empty array if no entries matched
     *
     * @api
     */
    public function findIdentifiersByTag($tag)
    {
        $tagsFiles = glob(GeneralUtility::getFileAbsFileName(self::RELATIVE_STORAGE_FOLDER).'*/*/*'.self::FILE_EXTENSION_TAG);
        $identifiers = [];
        foreach ($tagsFiles as $tagsFile) {
            if (false !== mb_strpos(file_get_contents($tagsFile), '|'.$tag.'|')) {
                $identifiers[] = file_get_contents(str_replace(self::FILE_EXTENSION_TAG, self::FILE_EXTENSION_IDENTIFIER, $tagsFile));
            }
        }

        return $identifiers;
    }

    /**
     * Freezes this cache backend.
     *
     * All data in a frozen backend remains unchanged and methods which try to add
     * or modify data result in an exception thrown. Possible expiry times of
     * individual cache entries are ignored.
     *
     * On the positive side, a frozen cache backend is much faster on read access.
     * A frozen backend can only be thawn by calling the flush() method.
     */
    public function freeze(): void
    {
        $this->freeze = true;
    }

    /**
     * Tells if this backend is frozen.
     *
     * @return bool
     */
    public function isFrozen()
    {
        return $this->freeze;
    }

    /**
     * Get filename.
     *
     * @throws \Exception
     */
    protected function getFileName(string $entryIdentifier): string
    {
        $urlParts = parse_url($entryIdentifier);
        if (isset($urlParts['path'])) {
            $pathInfo = PathUtility::pathinfo($urlParts['path']);
            if (isset($pathInfo['basename'])) {
                $baseName = urldecode($pathInfo['basename']);
            } elseif (isset($pathInfo['filename'])) {
                $baseName = urldecode($pathInfo['filename']);
            } else {
                throw new Exception('Could not fetch basename or filename of '.$entryIdentifier, 123678);
            }
        } else {
            throw new Exception('Could not fetch a valid path from identifier '.$entryIdentifier, 23478);
        }

        try {
            $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
            $storage = $resourceFactory->getDefaultStorage();
            $baseName = (string) $storage->sanitizeFileName($baseName);
        } catch (\Exception $exception) {
            $this->logger->warning('Could not sanitize the filename for remote_file backend: '.$exception->getMessage(), ['uri' => $entryIdentifier]);
        }

        // Hash
        $hash = substr(md5($entryIdentifier), 0, $this->hashLength);
        $remoteStructure = implode('/', str_split($hash));

        return $remoteStructure.'/'.$baseName;
    }
}

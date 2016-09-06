<?php
/**
 * Cache backend for static file cache
 *
 * @package SFC\NcStaticfilecache\Cache
 * @author  Tim Lochmüller
 */

namespace SFC\NcStaticfilecache\Cache;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Cache backend for static file cache
 *
 * This cache handle the file representation of the cache and handle
 * - CacheFileName
 * - CacheFileName.gz
 *
 * @author Tim Lochmüller
 */
class StaticFileBackend extends AbstractBackend
{

    /**
     * Cache directory
     *
     * @var string
     */
    protected $cacheDirectory = 'typo3temp/tx_ncstaticfilecache/';

    /**
     * Saves data in the cache.
     *
     * @param string $entryIdentifier An identifier for this specific cache entry
     * @param string $data The data to be stored
     * @param array $tags Tags to associate with this cache entry. If the backend does not support tags, this option can be ignored.
     * @param integer $lifetime Lifetime of this cache entry in seconds. If NULL is specified, the default lifetime is used. "0" means unlimited lifetime.
     *
     * @return void
     * @throws \TYPO3\CMS\Core\Cache\Exception if no cache frontend has been set.
     * @throws \TYPO3\CMS\Core\Cache\Exception\InvalidDataException if the data is not a string
     */
    public function set($entryIdentifier, $data, array $tags = [], $lifetime = null)
    {
        $databaseData = [
            'created' => $GLOBALS['EXEC_TIME'],
            'expires' => ($GLOBALS['EXEC_TIME'] + $this->getRealLifetime($lifetime)),
        ];
        if (in_array('explanation', $tags)) {
            $databaseData['explanation'] = $data;
            parent::set($entryIdentifier, serialize($databaseData), $tags, $lifetime);
            return;
        }

        // call set in front of the generation, because the set method
        // of the DB backend also call remove
        parent::set($entryIdentifier, serialize($databaseData), $tags, $lifetime);

        $fileName = $this->getCacheFilename($entryIdentifier);
        $cacheDir = PathUtility::pathinfo($fileName, PATHINFO_DIRNAME);
        if (!is_dir($cacheDir)) {
            GeneralUtility::mkdir_deep($cacheDir);
        }

        // normal
        GeneralUtility::writeFile($fileName, $data);

        // gz
        if ($this->configuration->get('enableStaticFileCompression')) {
            $contentGzip = gzencode($data, $this->getCompressionLevel());
            if ($contentGzip) {
                GeneralUtility::writeFile($fileName . '.gz', $contentGzip);
            }
        }

        // htaccess
        $this->writeHtAccessFile($fileName, $lifetime);
    }

    /**
     * Write htaccess file
     *
     * @param string $originalFileName
     * @param string $lifetime
     */
    protected function writeHtAccessFile($originalFileName, $lifetime)
    {
        if ($this->configuration->get('sendCacheControlHeader') || $this->configuration->get('sendCacheControlHeaderRedirectAfterCacheTimeout')) {
            $fileName = PathUtility::pathinfo($originalFileName, PATHINFO_DIRNAME) . '/.htaccess';
            $accessTimeout = $this->configuration->get('htaccessTimeout');
            $lifetime = $accessTimeout ? $accessTimeout : $this->getRealLifetime($lifetime);

            /** @var StandaloneView $renderer */
            $renderer = GeneralUtility::makeInstance(StandaloneView::class);
            $renderer->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName('EXT:nc_staticfilecache/Resources/Private/Templates/Htaccess.html'));
            $renderer->assignMultiple([
                'mode' => $accessTimeout ? 'A' : 'M',
                'lifetime' => $lifetime,
                'expires' => time() + $lifetime,
                'sendCacheControlHeader' => (bool)$this->configuration->get('sendCacheControlHeader'),
                'sendCacheControlHeaderRedirectAfterCacheTimeout' => (bool)$this->configuration->get('sendCacheControlHeaderRedirectAfterCacheTimeout'),
            ]);

            GeneralUtility::writeFile($fileName, $renderer->render());
        }
    }

    /**
     * Get the cache folder for the given entry
     *
     * @param $entryIdentifier
     *
     * @return string
     */
    protected function getCacheFilename($entryIdentifier)
    {
        // @todo check urldecode here, if the filesystem is not a UTF-8 filesystem
        $urlParts = parse_url($entryIdentifier);
        $cacheFilename = GeneralUtility::getFileAbsFileName($this->cacheDirectory . $urlParts['scheme'] . '/' . $urlParts['host'] . '/' . trim($urlParts['path'],
                '/'));
        $fileExtension = PathUtility::pathinfo(basename($cacheFilename), PATHINFO_EXTENSION);
        if (empty($fileExtension) || !GeneralUtility::inList($this->configuration->get('fileTypes'), $fileExtension)) {
            $cacheFilename = rtrim($cacheFilename, '/') . '/index.html';
        }
        return $cacheFilename;
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
            return null;
        }
        return unserialize(parent::get($entryIdentifier));
    }

    /**
     * Checks if a cache entry with the specified identifier exists.
     *
     * @param string $entryIdentifier An identifier specifying the cache entry
     *
     * @return boolean TRUE if such an entry exists, FALSE if not
     */
    public function has($entryIdentifier)
    {
        return is_file($this->getCacheFilename($entryIdentifier)) || parent::has($entryIdentifier);
    }

    /**
     * Removes all cache entries matching the specified identifier.
     * Usually this only affects one entry but if - for what reason ever -
     * old entries for the identifier still exist, they are removed as well.
     *
     * @param string $entryIdentifier Specifies the cache entry to remove
     *
     * @return boolean TRUE if (at least) an entry could be removed or FALSE if no entry was found
     */
    public function remove($entryIdentifier)
    {
        if (!$this->has($entryIdentifier)) {
            return false;
        }
        $this->removeStaticFiles($entryIdentifier);
        return parent::remove($entryIdentifier);
    }

    /**
     * Remove the static files of the given identifier
     *
     * @param $entryIdentifier
     */
    protected function removeStaticFiles($entryIdentifier)
    {
        $fileName = $this->getCacheFilename($entryIdentifier);
        $files = [
            $fileName,
            $fileName . '.gz',
            PathUtility::pathinfo($fileName, PATHINFO_DIRNAME) . '/.htaccess'
        ];
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    /**
     * Removes all cache entries of this cache.
     *
     * @return void
     */
    public function flush()
    {
        if ((boolean)$this->configuration->get('clearCacheForAllDomains') === false) {
            $this->flushByTag('sfc_domain_' . str_replace('.', '_', GeneralUtility::getIndpEnv('TYPO3_HOST_ONLY')));
            return;
        }
        $absoluteCacheDir = GeneralUtility::getFileAbsFileName($this->cacheDirectory);
        if (is_dir($absoluteCacheDir)) {
            $tempAbsoluteCacheDir = rtrim($absoluteCacheDir, '/') . '_' . GeneralUtility::milliseconds() . '/';
            rename($absoluteCacheDir, $tempAbsoluteCacheDir);
        }
        parent::flush();
        if (isset($tempAbsoluteCacheDir)) {
            GeneralUtility::rmdir($tempAbsoluteCacheDir, true);
        }
    }

    /**
     * Does garbage collection
     *
     * @return void
     */
    public function collectGarbage()
    {
        $cacheEntryIdentifiers = $this->getDatabaseConnection()
            ->exec_SELECTgetRows('DISTINCT identifier', $this->cacheTable, $this->expiredStatement);
        parent::collectGarbage();
        foreach ($cacheEntryIdentifiers as $row) {
            $this->removeStaticFiles($row['identifier']);
        }
    }

    /**
     * Removes all cache entries of this cache which are tagged by the specified tag.
     *
     * @param string $tag The tag the entries must have
     *
     * @return void
     */
    public function flushByTag($tag)
    {
        $identifiers = $this->findIdentifiersByTag($tag);
        foreach ($identifiers as $identifier) {
            $this->removeStaticFiles($identifier);
        }
        parent::flushByTag($tag);
    }
}

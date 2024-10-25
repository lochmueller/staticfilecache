<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Service;

use SFC\Staticfilecache\Cache\UriFrontend;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheGroupException;
use SFC\Staticfilecache\Domain\Repository\QueueRepository;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class CacheService
{
    /**
     * Get the StaticFileCache.
     *
     * @throws NoSuchCacheException
     */
    public function get(): FrontendInterface
    {
        return $this->getManager()->getCache('staticfilecache');
    }

    /**
     * Get the cache manager.
     */
    public function getManager(): CacheManager
    {
        return GeneralUtility::makeInstance(CacheManager::class);
    }

    /**
     * Get absolute base directory incl. ending slash.
     */
    public function getAbsoluteBaseDirectory(): string
    {
        $relativeDirectory = 'typo3temp/assets/tx_staticfilecache/';
        $overrideDirectory = trim((string) GeneralUtility::makeInstance(ConfigurationService::class)->get('overrideCacheDirectory'));
        if ('' !== $overrideDirectory) {
            $relativeDirectory = rtrim($overrideDirectory, '/') . '/';
        }

        $absolutePath = Environment::getPublicPath() . '/' . $relativeDirectory;

        return GeneralUtility::resolveBackPath($absolutePath);
    }

    /**
     * Flush the cache.
     *
     * @throws NoSuchCacheException
     * @throws NoSuchCacheGroupException
     */
    public function flush(bool $includeBoostQueue = false): void
    {
        if ($includeBoostQueue) {
            $configuration = GeneralUtility::makeInstance(ConfigurationService::class);
            $configuration->override('boostMode', '0');
        }
        $this->get()->flush();
        $this->getManager()->flushCachesInGroup('pages');

        if ($includeBoostQueue) {
            GeneralUtility::makeInstance(QueueRepository::class)->truncate();
        }
    }
}

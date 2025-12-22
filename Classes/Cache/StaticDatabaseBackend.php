<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Cache;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use SFC\Staticfilecache\Service\ConfigurationService;
use TYPO3\CMS\Core\Cache\Backend\Typo3DatabaseBackend;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * General Cache functions for StaticFileCache.
 */
abstract class StaticDatabaseBackend extends Typo3DatabaseBackend implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected ConfigurationService $configuration;

    /**
     * Constructs this backend.
     *
     * @param array  $options Configuration options - depends on the actual backend
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);
        $this->configuration = GeneralUtility::makeInstance(ConfigurationService::class);
    }

    /**
     * Set cache frontend instance and calculate data and tags table name.
     *
     * @param FrontendInterface $cache The frontend for this backend
     */
    public function setCache(FrontendInterface $cache): void
    {
        parent::setCache($cache);
        if ($this->configuration->isBool('renameTablesToOtherPrefix')) {
            $this->cacheTable = 'sfc_' . $this->cacheIdentifier;
            $this->tagsTable = 'sfc_' . $this->cacheIdentifier . '_tags';
        }
    }

    /**
     * Change the template to allow longer identifiers.
     */
    public function getTableDefinitions(): string
    {
        $large = $this->configuration->isBool('largeIdentifierInCacheTable') ? 'Large' : '';

        $cacheTableSql = file_get_contents(
            ExtensionManagementUtility::extPath('staticfilecache') .
            'Resources/Private/Sql/Cache/Backend/' . $large . 'Typo3DatabaseBackendCache.sql'
        );
        $requiredTableStructures = str_replace('###CACHE_TABLE###', $this->cacheTable, $cacheTableSql) . chr(10) . chr(10);
        $tagsTableSql = file_get_contents(
            ExtensionManagementUtility::extPath('staticfilecache') .
            'Resources/Private/Sql/Cache/Backend/' . $large . 'Typo3DatabaseBackendTags.sql'
        );

        return $requiredTableStructures . str_replace('###TAGS_TABLE###', $this->tagsTable, $tagsTableSql) . chr(10);
    }

    /**
     * Get the real life time.
     */
    protected function getRealLifetime(?int $lifetime): int
    {
        if (null === $lifetime) {
            $lifetime = $this->defaultLifetime;
        }
        if (0 === $lifetime || $lifetime > $this->maximumLifetime) {
            $lifetime = $this->maximumLifetime;
        }

        return (int) $lifetime;
    }
}

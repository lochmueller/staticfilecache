<?php

/**
 * General Cache functions for StaticFileCache.
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Cache;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use SFC\Staticfilecache\Service\ConfigurationService;
use TYPO3\CMS\Core\Cache\Backend\Typo3DatabaseBackend;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * General Cache functions for StaticFileCache.
 */
abstract class StaticDatabaseBackend extends Typo3DatabaseBackend implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * Configuration.
     */
    protected ConfigurationService $configuration;

    /**
     * Constructs this backend.
     *
     * @param string $context application context
     * @param array  $options Configuration options - depends on the actual backend
     */
    public function __construct($context, array $options = [])
    {
        parent::__construct($context, $options);
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
            $this->cacheTable = 'sfc_'.$this->cacheIdentifier;
            $this->tagsTable = 'sfc_'.$this->cacheIdentifier.'_tags';
        }
    }

    /**
     * Change the template to allow longer idenitifiers.
     *
     * @return string
     */
    public function getTableDefinitions()
    {
        $cacheTableSql = file_get_contents(
            ExtensionManagementUtility::extPath('staticfilecache').
            'Resources/Private/Sql/Cache/Backend/Typo3DatabaseBackendCache.sql'
        );
        $requiredTableStructures = str_replace('###CACHE_TABLE###', $this->cacheTable, $cacheTableSql).LF.LF;
        $tagsTableSql = file_get_contents(
            ExtensionManagementUtility::extPath('staticfilecache').
            'Resources/Private/Sql/Cache/Backend/Typo3DatabaseBackendTags.sql'
        );

        return $requiredTableStructures.str_replace('###TAGS_TABLE###', $this->tagsTable, $tagsTableSql).LF;
    }

    /**
     * Get the real life time.
     *
     * @param int $lifetime
     */
    protected function getRealLifetime($lifetime): int
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

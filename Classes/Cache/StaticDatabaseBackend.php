<?php

/**
 * General Cache functions for StaticFileCache.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Cache;

use Psr\Log\LoggerAwareTrait;
use SFC\Staticfilecache\Service\ConfigurationService;
use TYPO3\CMS\Core\Cache\Backend\Typo3DatabaseBackend;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

/**
 * General Cache functions for StaticFileCache.
 */
abstract class StaticDatabaseBackend extends Typo3DatabaseBackend
{
    use LoggerAwareTrait;

    /**
     * Configuration.
     *
     * @var ConfigurationService
     */
    protected $configuration;

    /**
     * Signal Slot dispatcher.
     *
     * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
     */
    protected $signalSlotDispatcher;

    /**
     * Signal class.
     *
     * @var string
     */
    protected $signalClass = '';

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
        $this->signalSlotDispatcher = GeneralUtility::makeInstance(Dispatcher::class);
        $this->signalClass = \get_class($this);
        $this->setLogger(GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__));
    }

    /**
     * Set cache frontend instance and calculate data and tags table name.
     *
     * @param FrontendInterface $cache The frontend for this backend
     */
    public function setCache(FrontendInterface $cache)
    {
        parent::setCache($cache);
        if ($this->configuration->isBool('renameTablesToOtherPrefix')) {
            $this->cacheTable = 'sfc_' . $this->cacheIdentifier;
            $this->tagsTable = 'sfc_' . $this->cacheIdentifier . '_tags';
        }
    }

    /**
     * Get the real life time.
     *
     * @param int $lifetime
     *
     * @return int
     */
    protected function getRealLifetime($lifetime): int
    {
        if (null === $lifetime) {
            $lifetime = $this->defaultLifetime;
        }
        if (0 === $lifetime || $lifetime > $this->maximumLifetime) {
            $lifetime = $this->maximumLifetime;
        }

        return (int)$lifetime;
    }

    /**
     * Call Dispatcher.
     *
     * @param string $signalName
     * @param array  $arguments
     *
     * @return array
     */
    protected function dispatch(string $signalName, array $arguments): array
    {
        try {
            return $this->signalSlotDispatcher->dispatch($this->signalClass, $signalName, $arguments);
        } catch (\Exception $exception) {
            $logger = GeneralUtility::makeInstance(LogManager::class)->getLogger($this->signalClass);
            $logger->error('Problems by calling signal: ' . $exception->getMessage() . ' / ' . $exception->getFile() . ':' . $exception->getLine());

            return $arguments;
        }
    }
}

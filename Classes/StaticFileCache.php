<?php

/**
 * Static File Cache.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache;

use SFC\Staticfilecache\Cache\UriFrontend;
use SFC\Staticfilecache\Service\CacheService;
use SFC\Staticfilecache\Service\ConfigurationService;
use SFC\Staticfilecache\Service\DateTimeService;
use SFC\Staticfilecache\Service\TagService;
use SFC\Staticfilecache\Service\UriService;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Static File Cache.
 */
class StaticFileCache implements StaticFileCacheSingletonInterface
{
    /**
     * Configuration of the extension.
     *
     * @var ConfigurationService
     */
    protected $configuration;

    /**
     * Cache.
     *
     * @var UriFrontend
     */
    protected $cache;

    /**
     * Cache.
     *
     * @var Dispatcher
     */
    protected $signalDispatcher;

    /**
     * Constructs this object.
     */
    public function __construct()
    {
        $this->cache = GeneralUtility::makeInstance(CacheService::class)->get();
        $this->signalDispatcher = GeneralUtility::makeInstance(Dispatcher::class);
        $this->configuration = GeneralUtility::makeInstance(ConfigurationService::class);
    }

    /**
     * Get the current object.
     *
     * @return StaticFileCache
     */
    public static function getInstance()
    {
        return GeneralUtility::makeInstance(self::class);
    }

    /**
     * Check if the SFC should create the cache.
     *
     * @param TypoScriptFrontendController $pObj        The parent object
     * @param int                          $timeOutTime The timestamp when the page times out
     */
    public function insertPageInCache(TypoScriptFrontendController $pObj, int $timeOutTime = 0)
    {
        $isStaticCached = false;

        $uri = GeneralUtility::makeInstance(UriService::class)->getUri();

        // Signal: Initialize variables before starting the processing.
        $preProcessArguments = [
            'frontendController' => $pObj,
            'uri' => $uri,
        ];
        $preProcessArguments = $this->dispatch('preProcess', $preProcessArguments);
        $uri = $preProcessArguments['uri'];

        // cache rules
        $ruleArguments = [
            'frontendController' => $pObj,
            'uri' => $uri,
            'explanation' => [],
            'skipProcessing' => false,
        ];
        $ruleArguments = $this->dispatch('cacheRule', $ruleArguments);
        $explanation = $ruleArguments['explanation'];

        if (!$ruleArguments['skipProcessing']) {
            if (0 === $timeOutTime) {
                $timeOutTime = $pObj->get_cache_timeout();
            }
            // If page has a endtime before the current timeOutTime, use it instead:
            if ($pObj->page['endtime'] > 0 && $pObj->page['endtime'] < $timeOutTime) {
                $timeOutTime = $pObj->page['endtime'];
            }
            $timeOutSeconds = $timeOutTime - (new DateTimeService())->getCurrentTime();

            // Don't continue if there is already an existing valid cache entry and we've got an invalid now.
            // Prevents overriding if a logged in user is checking the page in a second call
            // see https://forge.typo3.org/issues/67526
            if (\count($explanation) && $this->hasValidCacheEntry($uri)) {
                return;
            }

            $tagService = GeneralUtility::makeInstance(TagService::class);

            // The page tag pageId_NN is included in $pObj->pageCacheTags
            $cacheTags = $tagService->getTags();
            $cacheTags[] = 'sfc_pageId_' . $pObj->page['uid'];
            $cacheTags[] = 'sfc_domain_' . \str_replace('.', '_', \parse_url($uri, PHP_URL_HOST));

            // This is supposed to have "&& !$pObj->beUserLogin" in there as well
            // This fsck's up the ctrl-shift-reload hack, so I pulled it out.
            if (0 === \count($explanation)) {
                $content = $pObj->content;
                if ($this->configuration->get('showGenerationSignature')) {
                    $content .= "\n<!-- cached statically on: " . $this->formatTimestamp((new DateTimeService())->getCurrentTime()) . ' -->';
                    $content .= "\n<!-- expires on: " . $this->formatTimestamp($timeOutTime) . ' -->';
                }

                // Signal: Process content before writing to static cached file
                $contentArguments = [
                    'frontendController' => $pObj,
                    'uri' => $uri,
                    'content' => $content,
                    'timeOutSeconds' => $timeOutSeconds,
                ];
                $contentArguments = $this->dispatch(
                    'processContent',
                    $contentArguments
                );
                $content = $contentArguments['content'];
                $timeOutSeconds = $contentArguments['timeOutSeconds'];
                $uri = $contentArguments['uri'];
                $isStaticCached = true;

                $tagService->send();
            } else {
                $cacheTags[] = 'explanation';
                $content = $explanation;
                $timeOutSeconds = 0;
            }

            // create cache entry
            $this->cache->set($uri, $content, $cacheTags, $timeOutSeconds);
        }

        // Signal: Post process (no matter whether content was cached statically)
        $postProcessArguments = [
            'frontendController' => $pObj,
            'uri' => $uri,
            'isStaticCached' => $isStaticCached,
        ];
        $this->dispatch('postProcess', $postProcessArguments);
    }

    /**
     * Format the given timestamp.
     *
     * @param int $timestamp
     *
     * @return string
     */
    protected function formatTimestamp($timestamp): string
    {
        return \strftime($this->configuration->get('strftime'), $timestamp);
    }

    /**
     * Determines whether the given $uri has a valid cache entry.
     *
     * @param string $uri
     *
     * @return bool is available and valid
     */
    protected function hasValidCacheEntry($uri): bool
    {
        $entry = $this->cache->get($uri);

        return false !== $entry &&
            0 === \count($entry['explanation']) &&
            $entry['expires'] >= (new DateTimeService())->getCurrentTime();
    }

    /**
     * Call Dispatcher.
     *
     * @param string $signalName
     * @param array  $arguments
     *
     * @return mixed
     */
    protected function dispatch(string $signalName, array $arguments)
    {
        try {
            return $this->signalDispatcher->dispatch(__CLASS__, $signalName, $arguments);
        } catch (\Exception $exception) {
            $logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
            $logger->error('Problems by calling signal: ' . $exception->getMessage() . ' / ' . $exception->getFile() . ':' . $exception->getLine());

            return $arguments;
        }
    }
}

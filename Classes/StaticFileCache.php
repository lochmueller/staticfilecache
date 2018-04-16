<?php
/**
 * Static File Cache.
 */
declare(strict_types=1);

namespace SFC\Staticfilecache;

use Mso\IdnaConvert\IdnaConvert;
use SFC\Staticfilecache\Cache\UriFrontend;
use SFC\Staticfilecache\Service\CacheService;
use SFC\Staticfilecache\Service\ConfigurationService;
use SFC\Staticfilecache\Utility\DateTimeUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
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
     * Punycode / IDNA converter that is used to encode the URIs to ASCII.
     *
     * @var IdnaConvert
     */
    protected $idnaConverter;

    /**
     * Constructs this object.
     */
    public function __construct()
    {
        $this->cache = GeneralUtility::makeInstance(CacheService::class)->getCache();
        $this->signalDispatcher = GeneralUtility::makeInstance(Dispatcher::class);
        $this->configuration = GeneralUtility::makeInstance(ConfigurationService::class);
        $this->idnaConverter = GeneralUtility::makeInstance(IdnaConvert::class);
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
     *
     * @throws \TYPO3\CMS\Core\Cache\Exception\InvalidDataException
     */
    public function insertPageInCache(TypoScriptFrontendController &$pObj, &$timeOutTime)
    {
        $isStaticCached = false;
        $uri = $this->getUri();

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
            // If page has a endtime before the current timeOutTime, use it instead:
            if ($pObj->page['endtime'] > 0 && $pObj->page['endtime'] < $timeOutTime) {
                $timeOutTime = $pObj->page['endtime'];
            }
            $timeOutSeconds = $timeOutTime - DateTimeUtility::getCurrentTime();

            // Don't continue if there is already an existing valid cache entry and we've got an invalid now.
            // Prevents overriding if a logged in user is checking the page in a second call
            // see https://forge.typo3.org/issues/67526
            if (\count($explanation) && $this->hasValidCacheEntry($uri)) {
                return;
            }

            // The page tag pageId_NN is included in $pObj->pageCacheTags
            $cacheTags = ObjectAccess::getProperty($pObj, 'pageCacheTags', true);
            $cacheTags[] = 'sfc_pageId_' . $pObj->page['uid'];
            $cacheTags[] = 'sfc_domain_' . \str_replace('.', '_', \parse_url($uri, PHP_URL_HOST));

            // This is supposed to have "&& !$pObj->beUserLogin" in there as well
            // This fsck's up the ctrl-shift-reload hack, so I pulled it out.
            if (0 === \count($explanation)) {
                $content = $pObj->content;
                if ($this->configuration->get('showGenerationSignature')) {
                    $content .= "\n<!-- cached statically on: " . \strftime(
                        $this->configuration->get('strftime'),
                        DateTimeUtility::getCurrentTime()
                    ) . ' -->';
                    $content .= "\n<!-- expires on: " . \strftime(
                        $this->configuration->get('strftime'),
                        $timeOutTime
                    ) . ' -->';
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
     * get the URI for the current cache ident.
     *
     * @return string
     */
    protected function getUri()
    {
        // Find host-name / IP, always in lowercase:
        $isHttp = (0 === \mb_strpos(GeneralUtility::getIndpEnv('TYPO3_REQUEST_HOST'), 'http://'));
        $uri = GeneralUtility::getIndpEnv('REQUEST_URI');
        if ($this->configuration->isBool('recreateURI')) {
            $uri = $this->recreateUriPath($uri);
        }

        $uri = ($isHttp ? 'http://' : 'https://') . \mb_strtolower(GeneralUtility::getIndpEnv('HTTP_HOST')) . '/' . \ltrim($uri, '/');

        try {
            return $this->idnaConverter->encode($uri);
        } catch (\InvalidArgumentException $ex) {
            // The URI is already in puny code
            return $uri;
        }
    }

    /**
     * Recreates the URI of the current request.
     *
     * Especially in simulateStaticDocument context, the different URIs lead to the same result
     * and static file caching would store the wrong URI that was used in the first request to
     * the website (e.g. "TheGoodURI.13.0.html" is as well accepted as "TheFakeURI.13.0.html")
     *
     * @param string $uri
     *
     * @return string The recreated URI of the current request
     */
    protected function recreateUriPath($uri)
    {
        $objectManager = new ObjectManager();
        /** @var UriBuilder $uriBuilder */
        $uriBuilder = $objectManager->get(UriBuilder::class);
        if (null === ObjectAccess::getProperty($uriBuilder, 'contentObject', true)) {
            // there are situations without a valid contentObject in the URI builder
            // prevent this situation by return the original request URI
            return $uri;
        }
        $url = $uriBuilder->reset()
            ->setAddQueryString(true)
            ->setCreateAbsoluteUri(true)
            ->build();

        $parts = (array)\parse_url($url);
        $unset = ['scheme', 'user', 'pass', 'host', 'port'];
        foreach ($unset as $u) {
            unset($parts[$u]);
        }

        return HttpUtility::buildUrl($parts);
    }

    /**
     * Determines whether the given $uri has a valid cache entry.
     *
     * @param string $uri
     *
     * @return bool is available and valid
     */
    protected function hasValidCacheEntry($uri)
    {
        $entry = $this->cache->get($uri);

        return null !== $entry &&
            0 === \count($entry['explanation']) &&
            $entry['expires'] >= DateTimeUtility::getCurrentTime();
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
        return $this->signalDispatcher->dispatch(__CLASS__, $signalName, $arguments);
    }
}

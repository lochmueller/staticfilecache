<?php
/**
 * Static File Cache
 *
 * @package SFC\NcStaticfilecache
 * @author  Tim Lochmüller
 */

namespace SFC\NcStaticfilecache;

use SFC\NcStaticfilecache\Cache\UriFrontend;
use SFC\NcStaticfilecache\Utility\CacheUtility;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Static File Cache
 *
 * @author Michiel Roos
 * @author Tim Lochmüller
 */
class StaticFileCache implements SingletonInterface
{

    /**
     * Configuration of the extension
     *
     * @var Configuration
     */
    protected $configuration;

    /**
     * Cache
     *
     * @var UriFrontend
     */
    protected $cache;

    /**
     * Cache
     *
     * @var Dispatcher
     */
    protected $signalDispatcher;

    /**
     * Get the current object
     *
     * @return StaticFileCache
     */
    public static function getInstance()
    {
        return GeneralUtility::makeInstance(self::class);
    }

    /**
     * Constructs this object.
     */
    public function __construct()
    {
        $this->cache = CacheUtility::getCache();
        $this->signalDispatcher = GeneralUtility::makeInstance(Dispatcher::class);
        $this->configuration = GeneralUtility::makeInstance(Configuration::class);
    }

    /**
     * Check if the SFC should create the cache
     *
     * @param    TypoScriptFrontendController $pObj : The parent object
     * @param    string $timeOutTime : The timestamp when the page times out
     *
     * @return    void
     */
    public function insertPageIncache(TypoScriptFrontendController &$pObj, &$timeOutTime)
    {
        $isStaticCached = false;
        $uri = $this->getUri();

        // Signal: Initialize variables before starting the processing.
        $preProcessArguments = [
            'frontendController' => $pObj,
            'uri' => $uri,
        ];
        $preProcessArguments = $this->signalDispatcher->dispatch(__CLASS__, 'preProcess', $preProcessArguments);
        $uri = $preProcessArguments['uri'];

        // cache rules
        $ruleArguments = [
            'frontendController' => $pObj,
            'uri' => $uri,
            'explanation' => [],
            'skipProcessing' => false,
        ];
        $ruleArguments = $this->signalDispatcher->dispatch(__CLASS__, 'cacheRule', $ruleArguments);
        $explanation = $ruleArguments['explanation'];

        if (!$ruleArguments['skipProcessing']) {

            // Don't continue if there is already an existing valid cache entry and we've got an invalid now.
            // Prevents overriding if a logged in user is checking the page in a second call
            // see https://forge.typo3.org/issues/67526
            if (count($explanation) && $this->hasValidCacheEntry($uri)) {
                return;
            }

            // The page tag pageId_NN is included in $pObj->pageCacheTags
            $cacheTags = ObjectAccess::getProperty($pObj, 'pageCacheTags', true);
            $cacheTags[] = 'sfc_pageId_' . $pObj->page['uid'];
            $cacheTags[] = 'sfc_domain_' . str_replace('.', '_', parse_url($uri, PHP_URL_HOST));

            // This is supposed to have "&& !$pObj->beUserLogin" in there as well
            // This fsck's up the ctrl-shift-reload hack, so I pulled it out.
            if (sizeof($explanation) === 0) {

                // If page has a endtime before the current timeOutTime, use it instead:
                if ($pObj->page['endtime'] > 0 && $pObj->page['endtime'] < $timeOutTime) {
                    $timeOutTime = $pObj->page['endtime'];
                }

                $timeOutSeconds = $timeOutTime - $GLOBALS['EXEC_TIME'];

                $content = $pObj->content;
                if ($this->configuration->get('showGenerationSignature')) {
                    $content .= "\n<!-- cached statically on: " . strftime($this->configuration->get('strftime'),
                            $GLOBALS['EXEC_TIME']) . ' -->';
                    $content .= "\n<!-- expires on: " . strftime($this->configuration->get('strftime'),
                            $timeOutTime) . ' -->';
                }

                // Signal: Process content before writing to static cached file
                $processContentArguments = [
                    'frontendController' => $pObj,
                    'uri' => $uri,
                    'content' => $content,
                    'timeOutSeconds' => $timeOutSeconds,
                ];
                $processContentArguments = $this->signalDispatcher->dispatch(__CLASS__, 'processContent',
                    $processContentArguments);
                $content = $processContentArguments['content'];
                $timeOutSeconds = $processContentArguments['timeOutSeconds'];
                $uri = $processContentArguments['uri'];
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
        $this->signalDispatcher->dispatch(__CLASS__, 'postProcess', $postProcessArguments);
    }

    /**
     * get the URI for the current cache ident
     *
     * @return string
     */
    protected function getUri()
    {
        // Find host-name / IP, always in lowercase:
        $isHttp = (strpos(GeneralUtility::getIndpEnv('TYPO3_REQUEST_HOST'), 'http://') === 0);
        $uri = GeneralUtility::getIndpEnv('REQUEST_URI');
        if ($this->configuration->get('recreateURI')) {
            $uri = $this->recreateURI($uri);
        }
        return ($isHttp ? 'http://' : 'https://') . strtolower(GeneralUtility::getIndpEnv('HTTP_HOST')) . $uri;
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
     * @return    string        The recreated URI of the current request
     */
    protected function recreateURI($uri)
    {
        $objectManager = new ObjectManager();
        /** @var UriBuilder $uriBuilder */
        $uriBuilder = $objectManager->get(UriBuilder::class);
        if (ObjectAccess::getProperty($uriBuilder, 'contentObject', true) === null) {
            // there are situations without a valid contentObject in the URI builder
            // prevent this situation by return the original request URI
            return $uri;
        }
        $url = $uriBuilder->reset()
            ->setAddQueryString(true)
            ->build();

        return preg_replace('/https?:\/\/[^\/]+/is', '', $url);
    }

    /**
     * Determines whether the given $uri has a valid cache entry.
     *
     * @param     string  $uri
     *
     * @return    bool    is available and valid
     */
    protected function hasValidCacheEntry($uri)
    {
        $entry = $this->cache->get($uri);
        return ($entry !== null &&
                count($entry['explanation']) === 0 &&
                $entry['expires'] >= $GLOBALS['EXEC_TIME']);
    }
}

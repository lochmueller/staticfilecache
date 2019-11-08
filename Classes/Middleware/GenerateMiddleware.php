<?php

/**
 * GenerateMiddleware.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SFC\Staticfilecache\Cache\UriFrontend;
use SFC\Staticfilecache\Service\CacheService;
use SFC\Staticfilecache\Service\ConfigurationService;
use SFC\Staticfilecache\Service\DateTimeService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * GenerateMiddleware.
 */
class GenerateMiddleware implements MiddlewareInterface
{
    /**
     * Cache.
     *
     * @var UriFrontend
     */
    protected $cache;

    /**
     * Process an incoming server request.
     *
     * Processes an incoming server request in order to produce a response.
     * If unable to produce the response itself, it may delegate to the provided
     * request handler to do so.
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        if (!$response->hasHeader('X-SFC-Cachable')) {
            return $response;
        }

        if ($response->getStatusCode() !== 200) {
            return $response;
        }

        try {
            $this->cache = GeneralUtility::makeInstance(CacheService::class)->get();
        } catch (\Exception $exception) {
            return $response;
        }

        $debug = GeneralUtility::makeInstance(ConfigurationService::class)->isBool('debugHeaders');

        $uri = (string)$request->getUri();
        if (!$response->hasHeader('X-SFC-Explanation')) {
            if ($this->hasValidCacheEntry($uri) && !isset($_COOKIE['staticfilecache'])) {
                if ($debug) {
                    $response = $response->withHeader('X-SFC-State', 'TYPO3 - already in cache');
                }
                return $response;
            }
            $lifetime = $this->calculateLifetime($GLOBALS['TSFE']);
            if ($debug) {
                $response = $response->withHeader('X-SFC-State', 'TYPO3 - add to cache');
            }
        } else {
            $lifetime = 0;
            if ($debug) {
                $response = $response->withHeader('X-SFC-State', 'TYPO3 - no cache');
            }
        }

        $this->cache->set($uri, $response, (array)$response->getHeader('X-SFC-Tags'), $lifetime);

        return $response;
    }

    /**
     * Calculate timeout
     *
     * @param TypoScriptFrontendController $tsfe
     * @return int
     */
    protected function calculateLifetime(TypoScriptFrontendController $tsfe): int
    {
        if (!\is_array($tsfe->page)) {
            // $this->logger->warning('TSFE to not contains a valid page record?! Please check: https://github.com/lochmueller/staticfilecache/issues/150');
            return 0;
        }
        $timeOutTime = $tsfe->get_cache_timeout();

        // If page has a endtime before the current timeOutTime, use it instead:
        if ($tsfe->page['endtime'] > 0 && $tsfe->page['endtime'] < $timeOutTime) {
            $endtimeLifetime = $tsfe->page['endtime'] - time();
            if ($endtimeLifetime > 0) {
                $timeOutTime = $endtimeLifetime;
            }
        }
        return (int)$timeOutTime;
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
            empty($entry['explanation']) &&
            $entry['expires'] >= (new DateTimeService())->getCurrentTime();
    }
}

<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Middleware;

use Psr\Http\Message\RequestInterface;
use TYPO3\CMS\Core\Cache\CacheDataCollector;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SFC\Staticfilecache\Event\PreGenerateEvent;
use SFC\Staticfilecache\Service\CacheService;
use SFC\Staticfilecache\Service\ConfigurationService;
use SFC\Staticfilecache\Service\CookieService;
use SFC\Staticfilecache\Service\DateTimeService;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class GenerateMiddleware implements MiddlewareInterface
{
    protected ?FrontendInterface $cache = null;
    protected ServerRequestInterface $request;

    public function __construct(
        readonly protected EventDispatcherInterface $eventDispatcher,
        readonly protected CookieService            $cookieService,
        readonly protected Typo3Version             $typo3Version,
        readonly protected CacheService             $cacheService,
        readonly protected ConfigurationService             $configurationService
    ) {}

    /**
     * Process an incoming server request.
     *
     * Processes an incoming server request in order to produce a response.
     * If unable to produce the response itself, it may delegate to the provided
     * request handler to do so.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->request = $request;
        $response = $handler->handle($request);

        if (!$response->hasHeader('X-SFC-Cachable')) {
            return $this->removeSfcHeaders($response);
        }

        if (200 !== $response->getStatusCode()) {
            return $this->removeSfcHeaders($response);
        }

        try {
            $this->cache = $this->cacheService->get();
        } catch (\Exception $exception) {
            return $this->removeSfcHeaders($response);
        }

        $event = new PreGenerateEvent((string) $request->getUri(), $request, $response);
        $this->eventDispatcher->dispatch($event);
        $uri = $event->getUri();
        $response = $event->getResponse();
        if (!$response->hasHeader('X-SFC-Explanation')) {
            if ($this->hasValidCacheEntry($uri) && !$this->cookieService->hasCookie()) {
                $response = $response->withHeader('X-SFC-State', 'TYPO3 - already in cache');

                return $this->removeSfcHeaders($response);
            }
            $lifetime = $this->calculateLifetime($request, $response, $GLOBALS['TSFE']);
            $response = $response->withHeader('X-SFC-State', 'TYPO3 - add to cache');
        } else {
            $lifetime = 0;
            $response = $response->withHeader('X-SFC-State', 'TYPO3 - no cache');
        }

        $this->cache->set($uri, $response, (array) $response->getHeader('X-SFC-Tags'), $lifetime);

        return $this->removeSfcHeaders($response);
    }

    /**
     * Calculate timeout.
     */
    protected function calculateLifetime(RequestInterface $request, ResponseInterface $response, TypoScriptFrontendController $tsfe): int
    {
        if ($this->typo3Version->getMajorVersion() >= 13) {
            /** @var ServerRequest $request */
            /** @var CacheDataCollector $frontendCacheCollector */
            /* @phpstan-ignore-next-line */
            $frontendCacheCollector = $request->getAttribute('frontend.cache.collector');
            /* @phpstan-ignore-next-line */
            $resolvedLifetime = $frontendCacheCollector->resolveLifetime();

            $legacyTimeout = $this->getLegacyCacheTimeoutFromTsfe();

            if ($legacyTimeout > 0) {
                $timeOutTime = $legacyTimeout;
            } else {
                $timeOutTime = $resolvedLifetime;
            }

            return (int) $timeOutTime;
        }

        if (!\is_array($tsfe->page)) {
            // $this->logger->warning('TSFE to not contains a valid page record?! Please check: https://github.com/lochmueller/staticfilecache/issues/150');
            return 0;
        }

        /* @phpstan-ignore-next-line */
        $timeOutTime = $tsfe->get_cache_timeout();

        // If page has a endtime before the current timeOutTime, use it instead:
        if ($tsfe->page['endtime'] > 0 && ($tsfe->page['endtime'] - $GLOBALS['EXEC_TIME']) < $timeOutTime) {
            $endtimeLifetime = $tsfe->page['endtime'] - $GLOBALS['EXEC_TIME'];
            if ($endtimeLifetime > 0) {
                $timeOutTime = $endtimeLifetime;
            }
        }

        return (int) $timeOutTime;
    }


    protected function getLegacyCacheTimeoutFromTsfe(): int
    {
        if (!isset($GLOBALS['TSFE']) || !is_object($GLOBALS['TSFE'])) {
            return 0;
        }

        $tsfe = $GLOBALS['TSFE'];

        if (is_array($tsfe->page) && isset($tsfe->page['cache_timeout']) && (int)$tsfe->page['cache_timeout'] > 0) {
            return (int)$tsfe->page['cache_timeout'];
        }

        if (is_array($tsfe->config) && isset($tsfe->config['config']['cache_period']) && (int)$tsfe->config['config']['cache_period'] > 0) {
            return (int)$tsfe->config['config']['cache_period'];
        }

        return 0;
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

        return false !== $entry
            && empty($entry['explanation'])
            && $entry['expires'] >= (new DateTimeService())->getCurrentTime();
    }

    /**
     * Remove all Sfc headers.
     */
    protected function removeSfcHeaders(ResponseInterface $response): ResponseInterface
    {
        $debug = $this->configurationService->isBool('debugHeaders');
        if (!$debug) {
            $response = $response->withoutHeader('X-SFC-Cachable');
            $response = $response->withoutHeader('X-SFC-State');
            $response = $response->withoutHeader('X-SFC-Explanation');
            $response = $response->withoutHeader('X-SFC-Tags');
        }

        return $response;
    }
}

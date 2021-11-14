<?php

/**
 * GenerateMiddleware.
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Middleware;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SFC\Staticfilecache\Cache\UriFrontend;
use SFC\Staticfilecache\Event\PreGenerateEvent;
use SFC\Staticfilecache\Service\CacheService;
use SFC\Staticfilecache\Service\ConfigurationService;
use SFC\Staticfilecache\Service\CookieService;
use SFC\Staticfilecache\Service\DateTimeService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * GenerateMiddleware.
 */
class GenerateMiddleware implements MiddlewareInterface
{
    protected ?UriFrontend $cache = null;

    protected EventDispatcherInterface $eventDispatcher;

    /**
     * GenerateMiddleware constructor.
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Process an incoming server request.
     *
     * Processes an incoming server request in order to produce a response.
     * If unable to produce the response itself, it may delegate to the provided
     * request handler to do so.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        if (!$response->hasHeader('X-SFC-Cachable')) {
            return $this->removeSfcHeaders($response);
        }

        if (200 !== $response->getStatusCode()) {
            return $this->removeSfcHeaders($response);
        }

        try {
            $this->cache = GeneralUtility::makeInstance(CacheService::class)->get();
        } catch (\Exception $exception) {
            return $this->removeSfcHeaders($response);
        }

        $event = new PreGenerateEvent((string) $request->getUri(), $request, $response);
        $this->eventDispatcher->dispatch($event);
        $uri = $event->getUri();
        $response = $event->getResponse();
        if (!$response->hasHeader('X-SFC-Explanation')) {
            if ($this->hasValidCacheEntry($uri) && !isset($_COOKIE[CookieService::FE_COOKIE_NAME])) {
                $response = $response->withHeader('X-SFC-State', 'TYPO3 - already in cache');

                return $this->removeSfcHeaders($response);
            }
            $lifetime = $this->calculateLifetime($GLOBALS['TSFE']);
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

        return (int) $timeOutTime;
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
        $debug = GeneralUtility::makeInstance(ConfigurationService::class)->isBool('debugHeaders');
        if (!$debug) {
            $response = $response->withoutHeader('X-SFC-Cachable');
            $response = $response->withoutHeader('X-SFC-State');
            $response = $response->withoutHeader('X-SFC-Explanation');
            $response = $response->withoutHeader('X-SFC-Tags');
        }

        return $response;
    }
}

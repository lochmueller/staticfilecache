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

class GenerateMiddleware implements MiddlewareInterface
{
    protected ?FrontendInterface $cache = null;
    protected ServerRequestInterface $request;

    public function __construct(
        protected readonly EventDispatcherInterface $eventDispatcher,
        protected readonly CookieService            $cookieService,
        protected readonly Typo3Version             $typo3Version,
        protected readonly CacheService             $cacheService,
        protected readonly ConfigurationService             $configurationService
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
            $lifetime = $this->calculateLifetime($request, $response);
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
    protected function calculateLifetime(RequestInterface $request, ResponseInterface $response): int
    {
        /** @var ServerRequest $request */
        /** @var CacheDataCollector $frontendCacheCollector */
        /* @phpstan-ignore-next-line */
        $frontendCacheCollector = $request->getAttribute('frontend.cache.collector');
        /* @phpstan-ignore-next-line */
        return $frontendCacheCollector->resolveLifetime();
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

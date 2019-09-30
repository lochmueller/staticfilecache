<?php

/**
 * GenerateMiddleware.
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SFC\Staticfilecache\Cache\UriFrontend;
use SFC\Staticfilecache\Service\CacheService;
use SFC\Staticfilecache\Service\DateTimeService;
use TYPO3\CMS\Core\Http\SelfEmittableStreamInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
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

        try {
            $this->cache = GeneralUtility::makeInstance(CacheService::class)->get();
        } catch (\Exception $exception) {
            return $response;
        }

        $uri = (string) $request->getUri();

        // Don't continue if there is already an existing valid cache entry and we've got an invalid now.
        // Prevents overriding if a logged in user is checking the page in a second call
        // see https://forge.typo3.org/issues/67526
        if (!$response->hasHeader('X-SFC-Explanation') && $this->hasValidCacheEntry($uri)) {
            return $response;
        }

        if (!$response->hasHeader('X-SFC-Explanation')) {
            $content = (string) $response->getBody();
            $timeOutTime = $this->calculateTimeout($GLOBALS['TSFE']);
            $timeOutSeconds = $timeOutTime - (new DateTimeService())->getCurrentTime();
        } else {
            $content = implode(' ', $response->getHeader('X-SFC-Explanation'));
            $timeOutSeconds = 0;
        }

        $this->cache->set($uri, $content, (array) $response->getHeader('X-SFC-Tags'), $timeOutSeconds);

        return $response;
    }

    /**
     * Calculate timeout
     *
     * @param TypoScriptFrontendController $tsfe
     * @return int
     */
    protected function calculateTimeout(TypoScriptFrontendController $tsfe): int
    {
        if (!\is_array($tsfe->page)) {
            // $this->logger->warning('TSFE to not contains a valid page record?! Please check: https://github.com/lochmueller/staticfilecache/issues/150');
            return 0;
        }
        $timeOutTime = $tsfe->get_cache_timeout();

        // If page has a endtime before the current timeOutTime, use it instead:
        if ($tsfe->page['endtime'] > 0 && $tsfe->page['endtime'] < $timeOutTime) {
            $timeOutTime = $tsfe->page['endtime'];
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

        return false !== $entry &&
            empty($entry['explanation']) &&
            $entry['expires'] >= (new DateTimeService())->getCurrentTime();
    }
}

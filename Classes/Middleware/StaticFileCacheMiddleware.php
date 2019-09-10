<?php

/**
 * StaticFileCacheMiddleware.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SFC\Staticfilecache\Configuration;
use SFC\Staticfilecache\Service\MiddlewareService;
use SFC\Staticfilecache\StaticFileCache;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * StaticFileCacheMiddleware.
 */
class StaticFileCacheMiddleware implements MiddlewareInterface
{
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

        $configuration = Configuration::getConfiguration();
        $saveCacheHook = $configuration['saveCacheHook'] ?? '';
        if ('Middleware' === $saveCacheHook) {
            MiddlewareService::setResponse($response);
            $this->getStaticFileCache()->insertPageInCache($GLOBALS['TSFE']);
        }

        return $response;
    }

    /**
     * Get StaticFileCache object.
     *
     * @return StaticFileCache
     */
    protected function getStaticFileCache(): StaticFileCache
    {
        return GeneralUtility::makeInstance(StaticFileCache::class);
    }
}

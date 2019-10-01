<?php

/**
 * FallbackMiddleware
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SFC\Staticfilecache\Cache\IdentifierBuilder;
use SFC\Staticfilecache\Service\CacheService;
use SFC\Staticfilecache\Service\ConfigurationService;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * FallbackMiddleware
 */
class FallbackMiddleware implements MiddlewareInterface
{
    /**
     * Process the fallback middleware
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $config = GeneralUtility::makeInstance(ConfigurationService::class);
        try {
            if ($config->isBool('useFallbackMiddleware')) {
                return $this->handleViaFallback($request);
            }
        } catch (\Exception $exception) {
            // Not handled
        }
        return $handler->handle($request);
    }

    /**
     * Handle the fallback
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws \Exception
     */
    protected function handleViaFallback(ServerRequestInterface $request): ResponseInterface
    {
        $uri = $request->getUri();

        if ($uri->getQuery() !== '') {
            throw new \Exception('There should be no queries at the URI', 123678);
        }
        if ($request->getMethod() !== 'GET') {
            throw new \Exception('Only GET is handled', 72389);
        }
        if (isset($_COOKIE[$GLOBALS['TYPO3_CONF_VARS']['BE']['cookieName']])) {
            throw new \Exception('Only if there is no cookie', 627841);
        }
        if (isset($_COOKIE['staticfilecache']) && $_COOKIE['staticfilecache'] === 'fe_typo_user_logged_in') {
            throw new \Exception('StaticFileCache Cookie is set', 12738912);
        }

        $possibleStaticFile = GeneralUtility::makeInstance(IdentifierBuilder::class)->getFilepath((string)$uri);

        if (!is_file($possibleStaticFile) || !is_readable($possibleStaticFile)) {
            throw new \Exception('StaticFileCache file not found', 126371823);
        }

        // Check if we can support compressed files
        $headers = ['Content-Type' => 'text/html; charset=utf-8']; // 'X-sfc-fallback' => '1'
        foreach ($request->getHeader('accept-encoding') as $acceptEncoding) {
            if (strpos($acceptEncoding, 'gzip') !== false) {
                if (is_file($possibleStaticFile . '.gz') && is_readable($possibleStaticFile . '.gz')) {
                    $headers['Content-Encoding'] = 'gzip';
                    $possibleStaticFile .= '.gz';
                }
                break;
            }
        }

        $cacheDirectory = GeneralUtility::makeInstance(CacheService::class)->getAbsoluteBaseDirectory();
        if (strpos($possibleStaticFile, $cacheDirectory) !== 0) {
            throw new \Exception('The path is not in the cache directory', 348923472);
        }
        return new HtmlResponse(GeneralUtility::getUrl($possibleStaticFile), 200, $headers);
    }
}

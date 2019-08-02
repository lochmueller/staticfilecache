<?php

/**
 * StaticFileCacheFallbackMiddleware
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SFC\Staticfilecache\Service\ConfigurationService;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * StaticFileCacheFallbackMiddleware
 */
class StaticFileCacheFallbackMiddleware implements MiddlewareInterface
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
        if (!$config->isBool('useFallbackMiddleware')) {
            return $handler->handle($request);
        }

        try {
            return $this->handleViaFallback($request);
        } catch (\Exception $exception) {
            return $handler->handle($request);
        }
    }

    /**
     * Handle the fallback
     *
     * @param ServerRequestInterface $request
     * @throws \Exception
     * @return ResponseInterface|HtmlResponse
     */
    protected function handleViaFallback(ServerRequestInterface $request)
    {
        $uri = $request->getUri();

        $cacheDirectory = GeneralUtility::getFileAbsFileName('typo3temp/tx_staticfilecache/');
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

        $path = $cacheDirectory . $uri->getScheme() . DIRECTORY_SEPARATOR . $uri->getHost() . DIRECTORY_SEPARATOR . ($uri->getPort() ?: '80') . $uri->getPath() . DIRECTORY_SEPARATOR . 'index.html';
        $possibleStaticFile = realpath($path);
        if (false === $possibleStaticFile) {
            throw new \Exception('No possible StaticFileCache', 723894);
        }
        // Check if we can support compressed files
        $headers = ['Content-Type' => 'text/html; charset=utf-8'];
        foreach ($request->getHeader('accept-encoding') as $acceptEncoding) {
            if (strpos($acceptEncoding, 'gzip') !== false) {
                $headers['Content-Encoding'] = 'gzip';
                // $possibleStaticFile .= '.gz';
                break;
            }
        }

        // check if the file really is part of the cache directory
        if ($possibleStaticFile === false) {
            throw new \Exception('No possible StaticFileCache Part II', 54453459);
        }
        if (!is_file($possibleStaticFile) || !is_readable($possibleStaticFile)) {
            throw new \Exception('StaticFileCache file not found', 126371823);
        }
        if (strpos($possibleStaticFile, $cacheDirectory) !== 0) {
            throw new \Exception('The path is not in the cache directory', 348923472);
        }
        return new HtmlResponse(file_get_contents($possibleStaticFile), 200, $headers);
    }
}

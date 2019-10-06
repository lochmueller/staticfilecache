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
use SFC\Staticfilecache\Cache\Rule\AbstractRule;
use SFC\Staticfilecache\Service\CacheService;
use SFC\Staticfilecache\Service\ConfigurationService;
use SFC\Staticfilecache\Service\ObjectFactoryService;
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

        $explanation = [];
        $skipProcessing = false;
        foreach (GeneralUtility::makeInstance(ObjectFactoryService::class)->get('CacheRuleFallback') as $rule) {
            /** @var $rule AbstractRule */
            $rule->checkRule(null, $request, $explanation, $skipProcessing);
            if ($skipProcessing) {
                throw new \Exception('Could not use fallback, because: ' . implode(', ', $explanation), 1236781);
            }
        }

        if (isset($_COOKIE['staticfilecache']) && $_COOKIE['staticfilecache'] === 'fe_typo_user_logged_in') {
            throw new \Exception('StaticFileCache Cookie is set', 12738912);
        }

        $possibleStaticFile = GeneralUtility::makeInstance(IdentifierBuilder::class)->getFilepath((string)$uri);

        $headers = $this->getHeaders($request, $possibleStaticFile);

        if (!is_file($possibleStaticFile) || !is_readable($possibleStaticFile)) {
            throw new \Exception('StaticFileCache file not found', 126371823);
        }

        $cacheDirectory = GeneralUtility::makeInstance(CacheService::class)->getAbsoluteBaseDirectory();
        if (strpos($possibleStaticFile, $cacheDirectory) !== 0) {
            throw new \Exception('The path is not in the cache directory', 348923472);
        }

        return new HtmlResponse(GeneralUtility::getUrl($possibleStaticFile), 200, $headers);
    }

    /**
     * @param ServerRequestInterface $request
     * @param string $possibleStaticFile
     * @return array
     */
    protected function getHeaders(ServerRequestInterface $request, string &$possibleStaticFile)
    {
        $headers = [
            'Content-Type' => 'text/html; charset=utf-8',
        ];
        $config = $this->getCacheConfiguration($possibleStaticFile);
        if (isset($config->headers->{'Content-Type'})) {
            $headers['Content-Type'] = implode(', ', $config->headers->{'Content-Type'});
        }
        $debug = GeneralUtility::makeInstance(ConfigurationService::class)->isBool('debugHeaders');
        if ($debug) {
            $headers['X-SFC-State'] = 'StaticFileCache - via Fallback Middleware';
        }
        foreach ($request->getHeader('accept-encoding') as $acceptEncoding) {
            if (strpos($acceptEncoding, 'gzip') !== false) {
                if (is_file($possibleStaticFile . '.gz') && is_readable($possibleStaticFile . '.gz')) {
                    $headers['Content-Encoding'] = 'gzip';
                    $possibleStaticFile .= '.gz';
                }
                break;
            }
        }
        return $headers;
    }

    /**
     * Get cache configuration
     *
     * @param string $possibleStaticFile
     * @return array
     */
    protected function getCacheConfiguration(string $possibleStaticFile): array
    {
        $configFile = $possibleStaticFile . '.config.json';
        if (is_file($configFile) || !is_readable($configFile)) {
            return (array)json_decode((string)GeneralUtility::getUrl($configFile));
        }
        return [];
    }
}

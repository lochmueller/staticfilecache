<?php

/**
 * FallbackMiddleware.
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Middleware;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SFC\Staticfilecache\Cache\IdentifierBuilder;
use SFC\Staticfilecache\Event\CacheRuleFallbackEvent;
use SFC\Staticfilecache\Exception;
use SFC\Staticfilecache\Service\CacheService;
use SFC\Staticfilecache\Service\ConfigurationService;
use SFC\Staticfilecache\Service\CookieService;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * FallbackMiddleware.
 */
class FallbackMiddleware implements MiddlewareInterface
{
    protected EventDispatcherInterface$eventDispatcher;

    protected ConfigurationService $configurationService;

    public function __construct(EventDispatcherInterface $eventDispatcher, ConfigurationService $configurationService)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->configurationService = $configurationService;
    }

    /**
     * Process the fallback middleware.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            if ($this->configurationService->isBool('useFallbackMiddleware')) {
                return $this->handleViaFallback($request);
            }
        } catch (Exception $exception) {
            // Not handled
        }

        return $handler->handle($request);
    }

    /**
     * Handle the fallback.
     *
     * @throws \Exception
     */
    protected function handleViaFallback(ServerRequestInterface $request): ResponseInterface
    {
        $event = new CacheRuleFallbackEvent($request, [], false);
        $this->eventDispatcher->dispatch($event);

        if ($event->isSkipProcessing()) {
            throw new Exception('Could not use fallback, because: '.implode(', ', $event->getExplanation()), 1236781);
        }

        $uri = $request->getUri();

        if (isset($_COOKIE[CookieService::FE_COOKIE_NAME]) && 'typo_user_logged_in' === $_COOKIE[CookieService::FE_COOKIE_NAME]) {
            throw new Exception('StaticFileCache Cookie is set', 12738912);
        }

        $possibleStaticFile = GeneralUtility::makeInstance(IdentifierBuilder::class)->getFilepath((string) $uri);

        $headers = $this->getHeaders($event->getRequest(), $possibleStaticFile);

        if (!is_file($possibleStaticFile) || !is_readable($possibleStaticFile)) {
            throw new Exception('StaticFileCache file not found', 126371823);
        }

        $cacheDirectory = GeneralUtility::makeInstance(CacheService::class)->getAbsoluteBaseDirectory();
        if (0 !== strpos($possibleStaticFile, $cacheDirectory)) {
            throw new Exception('The path is not in the cache directory', 348923472);
        }

        return new HtmlResponse(GeneralUtility::getUrl($possibleStaticFile), 200, $headers);
    }

    /**
     * @return array
     */
    protected function getHeaders(ServerRequestInterface $request, string &$possibleStaticFile)
    {
        $headers = [
            'Content-Type' => 'text/html; charset=utf-8',
        ];
        $config = $this->getCacheConfiguration($possibleStaticFile);
        if (isset($config['headers']->{'Content-Type'})) {
            $headers['Content-Type'] = implode(', ', $config['headers']->{'Content-Type'});
        }
        $debug = $this->configurationService->isBool('debugHeaders');
        if ($debug) {
            $headers['X-SFC-State'] = 'StaticFileCache - via Fallback Middleware';
        }
        foreach ($request->getHeader('accept-encoding') as $acceptEncoding) {
            if (false !== strpos($acceptEncoding, 'br')) {
                if (is_file($possibleStaticFile.'.br') && is_readable($possibleStaticFile.'.br')) {
                    $headers['Content-Encoding'] = 'br';
                    $possibleStaticFile .= '.br';
                }

                break;
            }
            if (false !== strpos($acceptEncoding, 'gzip')) {
                if (is_file($possibleStaticFile.'.gz') && is_readable($possibleStaticFile.'.gz')) {
                    $headers['Content-Encoding'] = 'gzip';
                    $possibleStaticFile .= '.gz';
                }

                break;
            }
        }

        return $headers;
    }

    /**
     * Get cache configuration.
     */
    protected function getCacheConfiguration(string $possibleStaticFile): array
    {
        $configFile = $possibleStaticFile.'.config.json';
        if (is_file($configFile) || !is_readable($configFile)) {
            return (array) json_decode((string) GeneralUtility::getUrl($configFile));
        }

        return [];
    }
}

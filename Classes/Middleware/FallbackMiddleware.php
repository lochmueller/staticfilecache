<?php

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

class FallbackMiddleware implements MiddlewareInterface
{
    public function __construct(
        protected readonly EventDispatcherInterface $eventDispatcher,
        protected readonly ConfigurationService $configurationService,
        protected readonly IdentifierBuilder $identifierBuilder,
        protected readonly CacheService $cacheService,
    ) {}

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
            throw new Exception('Could not use fallback, because: ' . implode(', ', $event->getExplanation()), 1236781);
        }

        $uri = $request->getUri()->withQuery('');

        if (isset($_COOKIE[CookieService::FE_COOKIE_NAME]) && 'typo_user_logged_in' === $_COOKIE[CookieService::FE_COOKIE_NAME]) {
            throw new Exception('StaticFileCache Cookie is set', 12738912);
        }

        $possibleStaticFile = $this->identifierBuilder->getFilepath((string) $uri);

        $config = $this->getCacheConfiguration($possibleStaticFile);

        if (isset($config['invalidAtTimestamp']) && $config['invalidAtTimestamp'] < time()) {
            throw new Exception('Cache entry is old', 16237867241);
        }

        $headers = $this->getHeaders($event->getRequest(), $possibleStaticFile);

        if (!is_file($possibleStaticFile) || !is_readable($possibleStaticFile)) {
            throw new Exception('StaticFileCache file not found', 126371823);
        }

        $cacheDirectory = $this->cacheService->getAbsoluteBaseDirectory();
        if (!str_starts_with($possibleStaticFile, $cacheDirectory)) {
            throw new Exception('The path is not in the cache directory', 348923472);
        }

        return new HtmlResponse(GeneralUtility::getUrl($possibleStaticFile), 200, $headers);
    }

    protected function getHeaders(ServerRequestInterface $request, string &$possibleStaticFile): array
    {
        $headers = [
            'Content-Type' => 'text/html; charset=utf-8',
        ];
        $config = $this->getCacheConfiguration($possibleStaticFile);

        foreach ($config['headers'] ?? [] as $header => $value) {
            $headers[$header] = $value;
        }

        $debug = $this->configurationService->isBool('debugHeaders');
        if ($debug) {
            $headers['X-SFC-State'] = 'StaticFileCache - via Fallback Middleware';
        }

        // The delivered content depends on the Accept-Encoding header
        $vary = array_filter(array_map('trim', explode(',', (string) ($headers['Vary'] ?? ''))));
        if (!\in_array('accept-encoding', array_map('strtolower', $vary), true)) {
            $vary[] = 'Accept-Encoding';
        }
        $headers['Vary'] = implode(', ', $vary);

        $acceptedEncodings = $this->getAcceptedEncodings($request);
        foreach (['br' => '.br', 'gzip' => '.gz'] as $encoding => $extension) {
            if (\in_array($encoding, $acceptedEncodings, true) && is_file($possibleStaticFile . $extension) && is_readable($possibleStaticFile . $extension)) {
                $headers['Content-Encoding'] = $encoding;
                $possibleStaticFile .= $extension;
                break;
            }
        }

        return $headers;
    }

    /**
     * Get the accepted content encodings of the request (encodings with "q=0" are not accepted).
     *
     * @return string[]
     */
    protected function getAcceptedEncodings(ServerRequestInterface $request): array
    {
        $qualities = [];
        foreach (explode(',', $request->getHeaderLine('accept-encoding')) as $item) {
            $parameters = explode(';', $item);
            $encoding = strtolower(trim(array_shift($parameters)));
            if ('' === $encoding) {
                continue;
            }
            $quality = 1.0;
            foreach ($parameters as $parameter) {
                $parameter = strtolower(str_replace(' ', '', $parameter));
                if (str_starts_with($parameter, 'q=')) {
                    $quality = (float) substr($parameter, 2);
                }
            }
            $qualities[$encoding] = $quality;
        }

        $accepted = [];
        foreach (['br', 'gzip'] as $encoding) {
            $quality = $qualities[$encoding] ?? $qualities['*'] ?? 0.0;
            if ($quality > 0) {
                $accepted[] = $encoding;
            }
        }

        return $accepted;
    }

    /**
     * Get cache configuration.
     */
    protected function getCacheConfiguration(string $possibleStaticFile): array
    {
        $configFile = $possibleStaticFile . '.config.json';
        if (is_file($configFile) && is_readable($configFile)) {
            return (array) json_decode((string) GeneralUtility::getUrl($configFile), true);
        }

        return [];
    }
}

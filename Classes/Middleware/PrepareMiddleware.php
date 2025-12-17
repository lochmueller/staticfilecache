<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Middleware;

use TYPO3\CMS\Core\Cache\CacheTag;
use TYPO3\CMS\Core\Http\Stream;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SFC\Staticfilecache\Event\CacheRuleEvent;
use SFC\Staticfilecache\Service\ConfigurationService;
use SFC\Staticfilecache\Service\HttpPushService;
use SFC\Staticfilecache\Service\InlineAssetsService;

class PrepareMiddleware implements MiddlewareInterface
{
    public function __construct(
        protected readonly EventDispatcherInterface $eventDispatcher,
        protected readonly HttpPushService          $httpPushService,
        protected readonly ConfigurationService          $configurationService,
        protected readonly InlineAssetsService $inlineAssetsService
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
        $response = $handler->handle($request);

        $explanation = [];
        $skipProcessing = false;

        $event = new CacheRuleEvent($request, $explanation, $skipProcessing, $response);
        $this->eventDispatcher->dispatch($event);

        if (!$event->isSkipProcessing()) {

            $cacheDataCollector = $GLOBALS['TYPO3_REQUEST']->getAttribute('frontend.cache.collector');
            $cacheTags = array_map(fn(CacheTag $cacheTag) => $cacheTag->name, $cacheDataCollector->getCacheTags());


            if (false === $this->configurationService->isBool('clearCacheForAllDomains')) {
                $cacheTags[] = 'sfc_domain_' . str_replace('.', '_', $event->getRequest()->getUri()->getHost());
            }

            if (empty($event->getExplanation())) {
                $response = $response->withHeader('X-SFC-Cachable', '1');
            } else {
                $cacheTags[] = 'explanation';
                $response = $response->withHeader('X-SFC-Cachable', '0');
                foreach ($event->getExplanation() as $item) {
                    $response = $response->withAddedHeader('X-SFC-Explanation', $item);
                }
            }

            if (!empty($cacheTags)) {
                $response = $response->withHeader('X-SFC-Tags', $cacheTags);
            }
        }

        $processedHtml = (string) $this->inlineAssetsService->replaceInlineContent((string) $response->getBody());
        $responseBody = new Stream('php://temp', 'rw');
        $responseBody->write($processedHtml);
        $response = $response->withBody($responseBody);

        $pushHeaders = (array) $this->httpPushService->getHttpPushHeaders((string) $response->getBody());
        foreach ($pushHeaders as $pushHeader) {
            if (mb_detect_encoding($pushHeader['path'], 'ASCII', true)) {
                $value = '<' . $pushHeader['path'] . '>; rel=preload; as=' . $pushHeader['type'];

                // Font assets have to be preloaded using anonymous-mode CORS:
                // https://github.com/lochmueller/staticfilecache/issues/443
                if ($pushHeader['type'] === 'font') {
                    $value .= '; crossorigin';
                }

                $response = $response->withAddedHeader('Link', $value);
            }
        }

        return $response;
    }
}

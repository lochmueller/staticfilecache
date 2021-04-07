<?php

/**
 * PrepareMiddleware.
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Middleware;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SFC\Staticfilecache\Cache\Rule\AbstractRule;
use SFC\Staticfilecache\Event\CacheRuleEvent;
use SFC\Staticfilecache\Service\ConfigurationService;
use SFC\Staticfilecache\Service\HttpPushService;
use SFC\Staticfilecache\Service\ObjectFactoryService;
use SFC\Staticfilecache\Service\TypoScriptFrontendService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * PrepareMiddleware.
 */
class PrepareMiddleware implements MiddlewareInterface
{
    protected EventDispatcherInterface $eventDispatcher;

    /**
     * PrepareMiddleware constructor.
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

        $explanation = [];
        $skipProcessing = false;
        foreach (GeneralUtility::makeInstance(ObjectFactoryService::class)->get('CacheRule') as $rule) {
            // @var $rule AbstractRule
            $rule->checkRule($request, $explanation, $skipProcessing);
        }

        $event = new CacheRuleEvent($request, $explanation, $skipProcessing);
        $this->eventDispatcher->dispatch($event);

        if (!$event->isSkipProcessing()) {
            $cacheTags = GeneralUtility::makeInstance(TypoScriptFrontendService::class)->getTags();
            $configuration = GeneralUtility::makeInstance(ConfigurationService::class);
            if (false === (bool) $configuration->get('clearCacheForAllDomains')) {
                $cacheTags[] = 'sfc_domain_'.str_replace('.', '_', $event->getRequest()->getUri()->getHost());
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

        $pushHeaders = (array) GeneralUtility::makeInstance(HttpPushService::class)->getHttpPushHeaders((string) $response->getBody());
        foreach ($pushHeaders as $pushHeader) {
            $response = $response->withAddedHeader('Link', '<'.$pushHeader['path'].'>; rel=preload; as='.$pushHeader['type']);
        }

        return $response;
    }
}

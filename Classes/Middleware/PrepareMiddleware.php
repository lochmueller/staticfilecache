<?php

/**
 * PrepareMiddleware.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SFC\Staticfilecache\Cache\Rule\AbstractRule;
use SFC\Staticfilecache\Service\MiddlewareService;
use SFC\Staticfilecache\Service\ObjectFactoryService;
use SFC\Staticfilecache\Service\TagService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * PrepareMiddleware.
 */
class PrepareMiddleware implements MiddlewareInterface
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
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        // @todo migrate to complete middleware handling
        MiddlewareService::setResponse($response);

        $explanation = [];
        $skipProcessing = false;
        foreach (GeneralUtility::makeInstance(ObjectFactoryService::class)->get('CacheRule') as $rule) {
            /** @var $rule AbstractRule */
            $rule->checkRule($GLOBALS['TSFE'], $request, $explanation, $skipProcessing);
        }

        if (!$skipProcessing) {
            $tagService = GeneralUtility::makeInstance(TagService::class);

            $cacheTags = $tagService->getTags();
            $cacheTags[] = 'sfc_pageId_' . $GLOBALS['TSFE']->page['uid'];
            $cacheTags[] = 'sfc_domain_' . \str_replace('.', '_', $request->getUri()->getHost());

            if (empty($explanation)) {
                $response = $response->withHeader('X-SFC-Cachable', '1');
            } else {
                $cacheTags[] = 'explanation';
                $response = $response->withHeader('X-SFC-Cachable', '0');
                $response = $response->withHeader('X-SFC-Explanation', (string)$explanation);
            }

            $response = $response->withHeader('X-SFC-Tags', $cacheTags);
        }

        return $response;
    }
}

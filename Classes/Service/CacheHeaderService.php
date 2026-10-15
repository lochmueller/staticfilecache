<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Service;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;

/**
 * Cache headers of statically served entries, decided the way TYPO3 decides
 * them for its own responses via config.sendCacheHeaders and
 * config.sendCacheHeadersForSharedCaches.
 */
class CacheHeaderService
{
    /**
     * The cache header configuration of the page the request rendered.
     *
     * @return array{sendCacheHeaders: bool, sendCacheHeadersForSharedCaches: string}
     */
    public function getTypoScriptConfiguration(?ServerRequestInterface $request): array
    {
        $frontendTypoScript = $request?->getAttribute('frontend.typoscript');
        $configArray = $frontendTypoScript instanceof FrontendTypoScript ? $frontendTypoScript->getConfigArray() : [];

        return [
            'sendCacheHeaders' => (bool) ($configArray['sendCacheHeaders'] ?? false),
            'sendCacheHeadersForSharedCaches' => (string) ($configArray['sendCacheHeadersForSharedCaches'] ?? ''),
        ];
    }
}

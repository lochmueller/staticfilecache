<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Service;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\NormalizedParams;
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

    /**
     * Whether TYPO3 sends the shared-cache variant (max-age=0, s-maxage=<lifetime>):
     * always for "force", for "auto" when the request comes through a trusted
     * reverse proxy. Like in TYPO3, this takes precedence over sendCacheHeaders.
     */
    public function isForSharedCaches(string $sendCacheHeadersForSharedCaches, ServerRequestInterface $request): bool
    {
        if ('force' === $sendCacheHeadersForSharedCaches) {
            return true;
        }
        if ('auto' !== $sendCacheHeadersForSharedCaches) {
            return false;
        }
        $normalizedParams = $request->getAttribute('normalizedParams');
        if (!$normalizedParams instanceof NormalizedParams) {
            $normalizedParams = NormalizedParams::createFromRequest($request);
        }

        return $normalizedParams->isBehindReverseProxy();
    }

    /**
     * Expires and Cache-Control for an entry valid until $expires, counting down
     * the remaining lifetime as TYPO3 does when it serves from its page cache.
     *
     * @return array<string, string>
     */
    public function getHeaders(bool $sendCacheHeaders, bool $forSharedCaches, int $expires): array
    {
        if (!$forSharedCaches && !$sendCacheHeaders) {
            return [];
        }
        $lifetime = max(0, $expires - (new DateTimeService())->getCurrentTime());

        return [
            'Expires' => gmdate('D, d M Y H:i:s T', $expires),
            'Cache-Control' => $forSharedCaches ? 'max-age=0, s-maxage=' . $lifetime : 'max-age=' . $lifetime,
        ];
    }
}

<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Cache\Listener;

use SFC\Staticfilecache\Event\CacheRuleEvent;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * No no_cache.
 */
class NoNoCacheListener
{
    /**
     * No no_cache.
     */
    public function __invoke(CacheRuleEvent $event): void
    {
        $tsfe = $GLOBALS['TSFE'] ?? null;
        /* @phpstan-ignore-next-line */
        if ($tsfe instanceof TypoScriptFrontendController && $tsfe->no_cache) {
            $event->addExplanation(__CLASS__, 'config.no_cache is true');
        }
    }
}

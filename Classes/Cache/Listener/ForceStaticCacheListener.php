<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Cache\Listener;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ServerRequestInterface;
use SFC\Staticfilecache\Event\CacheRuleEvent;
use SFC\Staticfilecache\Event\ForceStaticFileCacheEvent;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3\CMS\Frontend\Page\PageInformation;

/**
 * Force the cache for special pages.
 */
class ForceStaticCacheListener
{
    public function __construct(protected readonly EventDispatcherInterface $eventDispatcher) {}

    public function __invoke(CacheRuleEvent $event): void
    {
        if ($event->isSkipProcessing() && $this->isForceCacheUri($event->getRequest())) {
            $event->setSkipProcessing(false);
            $event->truncateExplanations();

            $frontendTypoScript = $event->getRequest()->getAttribute('frontend.typoscript');
            if ($frontendTypoScript instanceof FrontendTypoScript) {
                $configArray = $frontendTypoScript->getConfigArray();
                if (!\is_array($configArray['INTincScript'])) {
                    // Avoid exceptions in recursivelyReplaceIntPlaceholdersInContent
                    $configArray['INTincScript'] = [];
                    $frontendTypoScript->setConfigArray($configArray);
                }

                // @todo
                // render the plugins in the output
                // v14??? $GLOBALS['TSFE']->INTincScript($event->getRequest());
            }
        }
    }

    /**
     * Is force cache URI?
     */
    protected function isForceCacheUri(ServerRequestInterface $request): bool
    {
        $pageInformation = $request->getAttribute('frontend.page.information');
        if (!$pageInformation instanceof PageInformation) {
            return false;
        }

        $forceStatic = (bool) ($pageInformation->getPageRecord()['tx_staticfilecache_cache_force'] ?? false);
        $event = new ForceStaticFileCacheEvent($forceStatic, $request);
        $this->eventDispatcher->dispatch($event);

        return $event->isForceStatic();
    }
}

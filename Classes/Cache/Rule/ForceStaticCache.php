<?php

/**
 * Force the cache for special pages.
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Cache\Rule;

use Psr\Http\Message\ServerRequestInterface;
use SFC\Staticfilecache\Event\ForceStaticFileCacheEvent;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Force the cache for special pages.
 */
class ForceStaticCache extends AbstractRule
{

    /**
     * @var \Psr\EventDispatcher\EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * PrepareMiddleware constructor.
     * @param \Psr\EventDispatcher\EventDispatcherInterface $eventDispatcher
     */
    public function __construct(\Psr\EventDispatcher\EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Ignore rule in force mode.
     *
     * @var array
     */
    protected $ignoreRules = [
        StaticCacheable::class,
        NoIntScripts::class,
        NoNoCache::class,
    ];

    /**
     * Method to check the rul and modify $explanation and/or $skipProcessing.
     *
     *
     * @param ServerRequestInterface $request
     * @param array $explanation
     * @param bool $skipProcessing
     */
    public function checkRule(ServerRequestInterface $request, array &$explanation, bool &$skipProcessing): void
    {
        if ($this->isForceCacheUri($GLOBALS['TSFE'], $request)) {
            foreach ($explanation as $key => $value) {
                foreach ($this->ignoreRules as $ignore) {
                    if (GeneralUtility::isFirstPartOfStr($key, $ignore)) {
                        unset($explanation[$key]);
                        continue;
                    }
                }
            }
            if (empty($explanation)) {
                // force the generation
                $skipProcessing = false;

                if (!\is_array($GLOBALS['TSFE']->config['INTincScript'])) {
                    // Avoid exceptions in recursivelyReplaceIntPlaceholdersInContent
                    $GLOBALS['TSFE']->config['INTincScript'] = [];
                }

                // render the plugins in the output
                $GLOBALS['TSFE']->INTincScript();
            }
        }
    }

    /**
     * Is force cache URI?
     *
     *
     * @param ServerRequestInterface $request
     *
     * @return bool
     */
    protected function isForceCacheUri(?TypoScriptFrontendController $frontendController, ServerRequestInterface $request): bool
    {
        if (!is_object($frontendController)) {
            return false;
        }

        $forceStatic = (bool)($frontendController->page['tx_staticfilecache_cache_force'] ?? false);
        $event = new ForceStaticFileCacheEvent($forceStatic, $frontendController, $request);
        $this->eventDispatcher->dispatch($event);

        return $event->isForceStatic();
    }
}

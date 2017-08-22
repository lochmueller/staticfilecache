<?php
/**
 * Force the cache for special pages.
 */
declare(strict_types=1);

namespace SFC\Staticfilecache\Cache\Rule;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Force the cache for special pages.
 */
class ForceStaticCache extends AbstractRule
{
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
     * @param TypoScriptFrontendController $frontendController
     * @param string                       $uri
     * @param array                        $explanation
     * @param bool                         $skipProcessing
     */
    public function checkRule(TypoScriptFrontendController $frontendController, string $uri, array &$explanation, bool &$skipProcessing)
    {
        if ($this->isForceCacheUri($frontendController, $uri)) {
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

                // render the plugins in the output
                $frontendController->INTincScript();
            }
        }
    }

    /**
     * Is force cache URI?
     *
     * @param TypoScriptFrontendController $frontendController
     * @param string                       $uri
     *
     * @return bool
     */
    protected function isForceCacheUri(TypoScriptFrontendController $frontendController, string $uri): bool
    {
        $signalSlotDispatcher = GeneralUtility::makeInstance(Dispatcher::class);
        $forceStatic = (bool) $frontendController->page['tx_staticfilecache_cache_force'];
        $params = [
            'forceStatic' => $forceStatic,
            'frontendController' => $frontendController,
            'uri' => $uri,
        ];
        $params = $signalSlotDispatcher->dispatch(__CLASS__, 'isForceCacheUri', $params);

        return (bool) $params['forceStatic'];
    }
}

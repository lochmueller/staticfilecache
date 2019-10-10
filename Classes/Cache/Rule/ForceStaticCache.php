<?php

/**
 * Force the cache for special pages.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Cache\Rule;

use Psr\Http\Message\ServerRequestInterface;
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
     * @param ServerRequestInterface $request
     * @param array                        $explanation
     * @param bool                         $skipProcessing
     */
    public function checkRule(?TypoScriptFrontendController $frontendController, ServerRequestInterface $request, array &$explanation, bool &$skipProcessing)
    {
        if ($this->isForceCacheUri($frontendController, $request)) {
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

                if (!\is_array($frontendController->config['INTincScript'])) {
                    // Avoid exceptions in recursivelyReplaceIntPlaceholdersInContent
                    $frontendController->config['INTincScript'] = [];
                }

                // render the plugins in the output
                $frontendController->INTincScript();
            }
        }
    }

    /**
     * Is force cache URI?
     *
     * @param TypoScriptFrontendController $frontendController
     * @param ServerRequestInterface                       $request
     *
     * @return bool
     */
    protected function isForceCacheUri(TypoScriptFrontendController $frontendController, ServerRequestInterface $request): bool
    {
        $signalSlotDispatcher = GeneralUtility::makeInstance(Dispatcher::class);
        $forceStatic = (bool)($frontendController->page['tx_staticfilecache_cache_force'] ?? false);
        $params = [
            'forceStatic' => $forceStatic,
            'frontendController' => $frontendController,
            'request' => $request,
        ];
        try {
            $params = $signalSlotDispatcher->dispatch(__CLASS__, 'isForceCacheUri', $params);
        } catch (\Exception $exception) {
            $this->logger->error('Problems by calling signal: ' . $exception->getMessage() . ' / ' . $exception->getFile() . ':' . $exception->getLine());
        }

        return (bool)$params['forceStatic'];
    }
}

<?php
/**
 * Check if the current page is static cachable in TSFE context.
 */
declare(strict_types=1);

namespace SFC\Staticfilecache\Cache\Rule;

use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Check if the current page is static cachable in TSFE context.
 */
class StaticCacheable extends AbstractRule
{
    /**
     * Check if the page is static cachable.
     *
     * @param TypoScriptFrontendController $frontendController
     * @param string                       $uri
     * @param array                        $explanation
     * @param bool                         $skipProcessing
     */
    public function checkRule(TypoScriptFrontendController $frontendController, string $uri, array &$explanation, bool &$skipProcessing)
    {
        if (!$this->isStaticCacheble($frontendController)) {
            $explanation[__CLASS__] = 'The page is not static chachable via TypoScriptFrontend';
        }
    }

    /**
     * Fix this bug: https://forge.typo3.org/issues/83212
     * @see TypoScriptFrontendController::isStaticCacheble
     *
     * @return bool TRUE if caching of current page is enabled (->isUserOrGroupSet() returns false even if no frontend user is logged in!)
     */
    public function isStaticCacheble(TypoScriptFrontendController $frontendController)
    {
        return !$frontendController->no_cache;
    }
}

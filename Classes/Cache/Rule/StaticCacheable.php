<?php
/**
 * Check if the current page is static cachable in TSFE context
 *
 * @package SFC\NcStaticfilecache\Cache\Rule
 * @author  Tim Lochmüller
 */

namespace SFC\NcStaticfilecache\Cache\Rule;

use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Check if the current page is static cachable in TSFE context
 *
 * @author Tim Lochmüller
 */
class StaticCacheable extends AbstractRule
{

    /**
     * Check if the page is static cachable
     *
     * @param TypoScriptFrontendController $frontendController
     * @param string $uri
     * @param array $explanation
     * @param bool $skipProcessing
     *
     * @return array
     */
    public function checkRule($frontendController, $uri, &$explanation, &$skipProcessing)
    {
        if (!$frontendController->isStaticCacheble()) {
            $explanation[__CLASS__] = 'The page is not static chachable via TypoScriptFrontend';
        }
    }
}

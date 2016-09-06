<?php
/**
 * Check if the current page is static cachable in Page property context
 *
 * @package SFC\NcStaticfilecache\Cache\Rule
 * @author  Tim Lochmüller
 */

namespace SFC\NcStaticfilecache\Cache\Rule;

use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Check if the current page is static cacheable in Page property context
 *
 * @author Tim Lochmüller
 */
class PageCacheable extends AbstractRule
{

    /**
     * Check if the current page is static cacheable in Page property context
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
        if (!$frontendController->page['tx_ncstaticfilecache_cache']) {
            $explanation[__CLASS__] = 'static cache disabled on page';
        }
    }
}

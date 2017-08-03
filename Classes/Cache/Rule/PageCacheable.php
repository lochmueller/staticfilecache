<?php
/**
 * Check if the current page is static cachable in Page property context
 *
 * @author  Tim Lochmüller
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Cache\Rule;

use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Check if the current page is static cacheable in Page property context
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
     */
    public function checkRule(TypoScriptFrontendController $frontendController, string $uri, array &$explanation, bool &$skipProcessing)
    {
        if (!$frontendController->page['tx_staticfilecache_cache']) {
            $explanation[__CLASS__] = 'static cache disabled on page';
        }
    }
}

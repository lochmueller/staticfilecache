<?php
/**
 * Check if the current page is static cachable in TSFE context
 *
 * @author  Tim Lochmüller
 */
declare(strict_types=1);

namespace SFC\Staticfilecache\Cache\Rule;

use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Check if the current page is static cachable in TSFE context
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
     */
    public function checkRule(TypoScriptFrontendController $frontendController, string $uri, array &$explanation, bool &$skipProcessing)
    {
        if (!$frontendController->isStaticCacheble()) {
            $explanation[__CLASS__] = 'The page is not static chachable via TypoScriptFrontend';
        }
    }
}

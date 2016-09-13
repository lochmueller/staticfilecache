<?php
/**
 * No no_cache
 *
 * @author  Tim Lochmüller
 */

namespace SFC\Staticfilecache\Cache\Rule;

use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * No no_cache
 */
class NoNoCache extends AbstractRule
{

    /**
     * No no_cache
     *
     * @param TypoScriptFrontendController $frontendController
     * @param string $uri
     * @param array $explanation
     * @param bool $skipProcessing
     */
    public function checkRule($frontendController, $uri, &$explanation, &$skipProcessing)
    {
        if ($frontendController->no_cache) {
            $explanation[__CLASS__] = 'config.no_cache is true';
        }
    }
}

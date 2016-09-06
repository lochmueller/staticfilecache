<?php
/**
 * No no_cache
 *
 * @package SFC\NcStaticfilecache\Cache\Rule
 * @author  Tim Lochmüller
 */

namespace SFC\NcStaticfilecache\Cache\Rule;

use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * No no_cache
 *
 * @author Tim Lochmüller
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
     *
     * @return array
     */
    public function checkRule($frontendController, $uri, &$explanation, &$skipProcessing)
    {
        if ($frontendController->no_cache) {
            $explanation[__CLASS__] = 'config.no_cache is true';
        }
    }
}

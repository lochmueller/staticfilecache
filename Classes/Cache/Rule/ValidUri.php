<?php
/**
 * Check if the URI is valid
 *
 * @author  Tim Lochmüller
 */

namespace SFC\Staticfilecache\Cache\Rule;

use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Check if the URI is valid
 */
class ValidUri extends AbstractRule
{

    /**
     * Check if the URI is valid
     *
     * @param TypoScriptFrontendController $frontendController
     * @param string $uri
     * @param array $explanation
     * @param bool $skipProcessing
     */
    public function checkRule($frontendController, $uri, &$explanation, &$skipProcessing)
    {
        // Note: A FILTER_VALIDATE_URL check is done in the URI frontend

        if (strpos($uri, '?') !== false) {
            $explanation[__CLASS__] = 'The URI contain a "?" that is not allowed for static file cache';
            $skipProcessing = true;
        }
        if (strpos($uri, 'index.php') !== false) {
            $explanation[__CLASS__] = 'The URI contain a "index.php" that is not allowed for static file cache';
            $skipProcessing = true;
        }
        if (strpos(parse_url($uri, PHP_URL_PATH), '//') !== false) {
            $explanation[__CLASS__] = 'Illegal link configuration. The URI should not contain a "//" ' .
                'because a folder name without name is not possible';
            $skipProcessing = true;
        }
    }
}

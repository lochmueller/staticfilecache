<?php

/**
 * Check if the current site is static cacheable.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Cache\Rule;

use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Check if the current site is static cacheable.
 */
class SiteCacheable extends AbstractRule
{
    /**
     * Check if the current site is static cacheable.
     *
     * @param TypoScriptFrontendController $frontendController
     * @param string                       $uri
     * @param array                        $explanation
     * @param bool                         $skipProcessing
     */
    public function checkRule(TypoScriptFrontendController $frontendController, string $uri, array &$explanation, bool &$skipProcessing)
    {
        if (!($GLOBALS['TYPO3_REQUEST'] instanceof \TYPO3\CMS\Core\Http\ServerRequest)) {
            return;
        }
        $site = $GLOBALS['TYPO3_REQUEST']->getAttribute('site');
        if (!($site instanceof \TYPO3\CMS\Core\Site\Entity\Site)) {
            return;
        }
        $config = $site->getConfiguration();
        if (isset($config['disableStaticFileCache']) && $config['disableStaticFileCache']) {
            $explanation[__CLASS__] = 'static cache disabled on site configuration: ' . $site->getIdentifier();
        }
    }
}

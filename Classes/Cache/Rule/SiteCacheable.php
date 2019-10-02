<?php

/**
 * Check if the current site is static cacheable.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Cache\Rule;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Site\Entity\Site;
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
     * @param ServerRequestInterface $request
     * @param array                        $explanation
     * @param bool                         $skipProcessing
     */
    public function checkRule(?TypoScriptFrontendController $frontendController, ServerRequestInterface $request, array &$explanation, bool &$skipProcessing)
    {
        $site = $request->getAttribute('site');
        if (!($site instanceof Site)) {
            return;
        }
        $config = $site->getConfiguration();
        if (isset($config['disableStaticFileCache']) && $config['disableStaticFileCache']) {
            $explanation[__CLASS__] = 'static cache disabled on site configuration: ' . $site->getIdentifier();
        }
    }
}

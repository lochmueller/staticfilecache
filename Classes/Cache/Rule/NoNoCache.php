<?php

/**
 * No no_cache.
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Cache\Rule;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * No no_cache.
 */
class NoNoCache extends AbstractRule
{
    /**
     * No no_cache.
     */
    public function checkRule(ServerRequestInterface $request, array &$explanation, bool &$skipProcessing): void
    {
        if ($GLOBALS['TSFE'] instanceof TypoScriptFrontendController && $GLOBALS['TSFE']->no_cache) {
            $explanation[__CLASS__] = 'config.no_cache is true';
        }
    }
}

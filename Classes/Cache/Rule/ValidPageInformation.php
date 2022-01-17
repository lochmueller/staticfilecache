<?php

/**
 * ValidPageInformation.
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Cache\Rule;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * ValidPageInformation.
 *
 * @see https://github.com/lochmueller/staticfilecache/issues/150
 */
class ValidPageInformation extends AbstractRule
{
    /**
     * ValidPageInformation.
     */
    public function checkRule(ServerRequestInterface $request, array &$explanation, bool &$skipProcessing): void
    {
        $tsfe = $GLOBALS['TSFE'] ?? null;
        if (!$tsfe instanceof TypoScriptFrontendController || !\is_array($tsfe->page) || !$tsfe->page['uid']) {
            $skipProcessing = true;
            $explanation[__CLASS__] = 'There is no valid page in the TSFE';
        }
    }
}

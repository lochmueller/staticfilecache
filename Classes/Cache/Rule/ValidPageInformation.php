<?php

/**
 * ValidPageInformation.
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Cache\Rule;

use Psr\Http\Message\ServerRequestInterface;

/**
 * ValidPageInformation.
 *
 * @see https://github.com/lochmueller/staticfilecache/issues/150
 */
class ValidPageInformation extends AbstractRule
{
    /**
     * ValidPageInformation.
     *
     *
     * @param ServerRequestInterface $request
     * @param array                        $explanation
     * @param bool                         $skipProcessing
     */
    public function checkRule(ServerRequestInterface $request, array &$explanation, bool &$skipProcessing): void
    {
        if (!\is_array($GLOBALS['TSFE']->page) || !$GLOBALS['TSFE']->page['uid']) {
            $skipProcessing = true;
            $explanation[__CLASS__] = 'There is no valid page in the TSFE';
        }
    }
}

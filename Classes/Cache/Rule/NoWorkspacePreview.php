<?php

/**
 * No workspace preview.
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Cache\Rule;

use Psr\Http\Message\ServerRequestInterface;

/**
 * No workspace preview.
 */
class NoWorkspacePreview extends AbstractRule
{
    /**
     * Check if it is no workspace preview.
     */
    public function checkRule(ServerRequestInterface $request, array &$explanation, bool &$skipProcessing): void
    {
        if (\is_object($GLOBALS['TSFE']) && $GLOBALS['TSFE']->doWorkspacePreview()) {
            $explanation[__CLASS__] = 'The page is in workspace preview mode';
        }
    }
}

<?php

/**
 * Check if the doktype is valid.
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Cache\Rule;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Check if the doktype is valid.
 */
class ValidDoktype extends AbstractRule
{
    /**
     * Check if the URI is valid.
     *
     *
     * @param ServerRequestInterface $request
     * @param array                        $explanation
     * @param bool                         $skipProcessing
     */
    public function checkRule(ServerRequestInterface $request, array &$explanation, bool &$skipProcessing): void
    {
        if (!isset($GLOBALS['TSFE']->page)) {
            $explanation[__CLASS__] = 'There is no valid page in the frontendController object';
            $skipProcessing = true;
            return;
        }

        $ignoreTypes = [
            3, // DOKTYPE_LINK,
            254, // DOKTYPE_SYSFOLDER,
            255, // DOKTYPE_RECYCLER,
        ];

        $currentType = (int)($GLOBALS['TSFE']->page['doktype'] ?? 1);
        if (\in_array($currentType, $ignoreTypes, true)) {
            $explanation[__CLASS__] = 'The Page doktype ' . $currentType . ' is one of the following not allowed numbers: ' . \implode(
                ', ',
                $ignoreTypes
            );
            $skipProcessing = true;
        }
    }
}

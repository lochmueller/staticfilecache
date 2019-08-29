<?php

/**
 * Check if the doktype is valid.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Cache\Rule;

use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Page\PageRepository;

/**
 * Check if the doktype is valid.
 */
class ValidDoktype extends AbstractRule
{
    /**
     * Check if the URI is valid.
     *
     * @param TypoScriptFrontendController $frontendController
     * @param string                       $uri
     * @param array                        $explanation
     * @param bool                         $skipProcessing
     */
    public function checkRule(TypoScriptFrontendController $frontendController, string $uri, array &$explanation, bool &$skipProcessing)
    {
        $ignoreTypes = [
            PageRepository::DOKTYPE_LINK,
            PageRepository::DOKTYPE_SYSFOLDER,
            PageRepository::DOKTYPE_RECYCLER,
        ];
        if (isset($frontendController->page)) {
            $currentType = (int)$frontendController->page['doktype'];
            if (\in_array($currentType, $ignoreTypes, true)) {
                $explanation[__CLASS__] = 'The Page doktype ' . $currentType . ' is one of the following not allowed numbers: ' . \implode(
                    ', ',
                    $ignoreTypes
                );
                $skipProcessing = true;
            }
        } else {
            $skipProcessing = true;
        }
    }
}

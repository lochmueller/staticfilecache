<?php

/**
 * Enable.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Cache\Rule;

use Psr\Http\Message\ServerRequestInterface;
use SFC\Staticfilecache\Service\ConfigurationService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Enable.
 */
class Enable extends AbstractRule
{
    /**
     * Enable.
     *
     * @param TypoScriptFrontendController $frontendController
     * @param ServerRequestInterface $requesti
     * @param array                        $explanation
     * @param bool                         $skipProcessing
     */
    public function checkRule(?TypoScriptFrontendController $frontendController, ServerRequestInterface $request, array &$explanation, bool &$skipProcessing)
    {
        /** @var ConfigurationService $configuration */
        $configuration = GeneralUtility::makeInstance(ConfigurationService::class);
        if ($configuration->isBool('disableCache')) {
            $explanation[__CLASS__] = 'static cache disabled by TypoScript';
        }
    }
}

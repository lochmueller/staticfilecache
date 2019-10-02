<?php

/**
 * No active BE user.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Cache\Rule;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * No active BE user.
 */
class NoBackendUser extends AbstractRule
{
    /**
     * No active BE user.
     *
     * @param TypoScriptFrontendController $frontendController
     * @param ServerRequestInterface $request
     * @param array $explanation
     * @param bool $skipProcessing
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     */
    public function checkRule(?TypoScriptFrontendController $frontendController, ServerRequestInterface $request, array &$explanation, bool &$skipProcessing)
    {
        $context = GeneralUtility::makeInstance(Context::class);
        if ($context->getPropertyFromAspect('backend.user', 'isLoggedIn', false)) {
            $skipProcessing = true;
            $explanation[__CLASS__] = 'Active BE Login (TSFE:beUserLogin)';
        }
    }
}

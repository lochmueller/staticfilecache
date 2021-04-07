<?php

/**
 * Enable.
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Cache\Rule;

use Psr\Http\Message\ServerRequestInterface;
use SFC\Staticfilecache\Service\ConfigurationService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Enable.
 */
class Enable extends AbstractRule
{
    /**
     * Enable.
     */
    public function checkRule(ServerRequestInterface $request, array &$explanation, bool &$skipProcessing): void
    {
        /** @var ConfigurationService $configuration */
        $configuration = GeneralUtility::makeInstance(ConfigurationService::class);
        if ($configuration->isBool('disableCache')) {
            $explanation[__CLASS__] = 'static cache disabled by TypoScript';
        }
    }
}

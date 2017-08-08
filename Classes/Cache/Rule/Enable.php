<?php
/**
 * Enable
 *
 * @author  Tim Lochmüller
 */
declare(strict_types=1);

namespace SFC\Staticfilecache\Cache\Rule;

use SFC\Staticfilecache\Service\ConfigurationService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Enable
 */
class Enable extends AbstractRule
{

    /**
     * Enable
     *
     * @param TypoScriptFrontendController $frontendController
     * @param string $uri
     * @param array $explanation
     * @param bool $skipProcessing
     */
    public function checkRule(TypoScriptFrontendController $frontendController, string $uri, array &$explanation, bool &$skipProcessing)
    {
        /** @var ConfigurationService $configuration */
        $configuration = GeneralUtility::makeInstance(ConfigurationService::class);
        if ($configuration->isBool('disableCache')) {
            $explanation[__CLASS__] = 'static cache disabled by TypoScript';
        }
    }
}

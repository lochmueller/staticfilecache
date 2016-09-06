<?php
/**
 * Enable
 *
 * @package SFC\Staticfilecache\Cache\Rule
 * @author  Tim Lochmüller
 */

namespace SFC\Staticfilecache\Cache\Rule;

use SFC\Staticfilecache\Configuration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Enable
 *
 * @author Tim Lochmüller
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
     *
     * @return array
     */
    public function checkRule($frontendController, $uri, &$explanation, &$skipProcessing)
    {
        /** @var Configuration $configuration */
        $configuration = GeneralUtility::makeInstance(Configuration::class);
        if ((boolean)$configuration->get('disableCache') === true) {
            $explanation[__CLASS__] = 'static cache disabled by TypoScript';
        }
    }
}

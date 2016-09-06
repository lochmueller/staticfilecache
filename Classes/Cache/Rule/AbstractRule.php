<?php
/**
 * Abstract Rule
 *
 * @package SFC\NcStaticfilecache\Cache\Rule
 * @author  Tim Lochmüller
 */

namespace SFC\NcStaticfilecache\Cache\Rule;

use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Abstract Rule
 *
 * @author Tim Lochmüller
 */
abstract class AbstractRule
{

    /**
     * Wrapper for the signal
     *
     * @param TypoScriptFrontendController $frontendController
     * @param string $uri
     * @param array $explanation
     * @param bool $skipProcessing
     *
     * @return array
     */
    public function check($frontendController, $uri, $explanation, $skipProcessing)
    {
        $this->checkRule($frontendController, $uri, $explanation, $skipProcessing);
        return [
            'frontendController' => $frontendController,
            'uri' => $uri,
            'explanation' => $explanation,
            'skipProcessing' => $skipProcessing,
        ];
    }

    /**
     * Method to check the rul and modify $explanation and/or $skipProcessing
     *
     * @param TypoScriptFrontendController $frontendController
     * @param string $uri
     * @param array $explanation
     * @param bool $skipProcessing
     */
    abstract protected function checkRule($frontendController, $uri, &$explanation, &$skipProcessing);
}

<?php
/**
 * Abstract Rule.
 */
declare(strict_types = 1);

namespace SFC\Staticfilecache\Cache\Rule;

use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Abstract Rule.
 */
abstract class AbstractRule
{
    /**
     * Wrapper for the signal.
     *
     * @param TypoScriptFrontendController $frontendController
     * @param string                       $uri
     * @param array                        $explanation
     * @param bool                         $skipProcessing
     *
     * @return array
     */
    public function check(TypoScriptFrontendController $frontendController, string $uri, array $explanation, bool $skipProcessing): array
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
     * Method to check the rul and modify $explanation and/or $skipProcessing.
     *
     * @param TypoScriptFrontendController $frontendController
     * @param string                       $uri
     * @param array                        $explanation
     * @param bool                         $skipProcessing
     */
    abstract protected function checkRule(TypoScriptFrontendController $frontendController, string $uri, array &$explanation, bool &$skipProcessing);
}

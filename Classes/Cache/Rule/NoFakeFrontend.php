<?php

/**
 * No fake frontend.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Cache\Rule;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Utility\StringUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * No fake frontend.
 */
class NoFakeFrontend extends AbstractRule
{
    /**
     * No fake frontend.
     *
     * @param TypoScriptFrontendController $frontendController
     * @param ServerRequestInterface $request
     * @param array                        $explanation
     * @param bool                         $skipProcessing
     */
    public function checkRule(?TypoScriptFrontendController $frontendController, ServerRequestInterface $request, array &$explanation, bool &$skipProcessing)
    {
        $ignorePaths = [
            // Solr extension
            '/solr/Classes/Eid/Suggest.php',
            '/solr/Classes/Util.php',
        ];
        foreach ($ignorePaths as $ignorePath) {
            foreach ($this->getCallPaths() as $path) {
                if (StringUtility::endsWith($path, $ignorePath)) {
                    $skipProcessing = true;

                    return;
                }
            }
        }
    }

    /**
     * Get all call paths.
     *
     * @return array
     */
    protected function getCallPaths(): array
    {
        $paths = [];
        $backTrace = \debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        foreach ($backTrace as $value) {
            if (isset($value['file'])) {
                $paths[] = $value['file'];
            }
        }

        return $paths;
    }
}

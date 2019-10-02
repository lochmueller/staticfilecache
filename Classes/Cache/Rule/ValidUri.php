<?php

/**
 * Check if the URI is valid.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Cache\Rule;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Check if the URI is valid.
 */
class ValidUri extends AbstractRule
{

    /**
     * Check if the URI is valid
     * Note: A "valid URL" check is already done in the URI frontend.
     *
     * @param TypoScriptFrontendController $frontendController
     * @param ServerRequestInterface $request
     * @param array $explanation
     * @param bool $skipProcessing
     */
    public function checkRule(?TypoScriptFrontendController $frontendController, ServerRequestInterface $request, array &$explanation, bool &$skipProcessing)
    {
        $uri = (string)$request->getUri();
        if (false !== \mb_strpos($uri, '?')) {
            $explanation[__CLASS__] = 'The URI contain a "?" that is not allowed for StaticFileCache';
            $skipProcessing = true;
        } elseif (false !== \mb_strpos($uri, 'index.php')) {
            $explanation[__CLASS__] = 'The URI contain a "index.php" that is not allowed for StaticFileCache';
            $skipProcessing = true;
        } elseif (false !== \mb_strpos(\parse_url($uri, PHP_URL_PATH), '//')) {
            $explanation[__CLASS__] = 'Illegal link configuration. The URI should not contain a "//" ' .
                'because a folder name without name is not possible';
            $skipProcessing = true;
        }
    }
}

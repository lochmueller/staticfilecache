<?php

/**
 * Check if there is no path segment that is to long.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Cache\Rule;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Check if there is no path segment that is to long.
 */
class NoLongPathSegment extends AbstractRule
{
    /**
     * Check if there is no path segment that is to long.
     *
     * @param TypoScriptFrontendController $frontendController
     * @param ServerRequestInterface $request
     * @param array $explanation
     * @param bool $skipProcessing
     */
    public function checkRule(?TypoScriptFrontendController $frontendController, ServerRequestInterface $request, array &$explanation, bool &$skipProcessing)
    {
        $uri = (string)$request->getUri();
        $path = (string)\parse_url($uri, PHP_URL_PATH);
        $segments = explode('/', $path);

        foreach ($segments as $segment) {
            if (strlen($segment) > 255) {
                $explanation[__CLASS__] = 'The URI seegment of the URI is to long to create a folder based on tthis segment: ' . $segment;
                $skipProcessing = true;
                return;
            }
        }
    }
}

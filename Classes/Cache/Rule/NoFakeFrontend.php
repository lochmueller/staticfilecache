<?php

/**
 * No fake frontend.
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Cache\Rule;

use Psr\Http\Message\ServerRequestInterface;

/**
 * No fake frontend.
 */
class NoFakeFrontend extends AbstractRule
{
    /**
     * No fake frontend.
     */
    public function checkRule(ServerRequestInterface $request, array &$explanation, bool &$skipProcessing): void
    {
        $ignorePaths = [
            // Solr extension
            '/solr/Classes/Eid/Suggest.php',
            '/solr/Classes/Util.php',
        ];
        foreach ($ignorePaths as $ignorePath) {
            foreach ($this->getCallPaths() as $path) {
                if (str_ends_with($path, $ignorePath)) {
                    $skipProcessing = true;
                    $explanation[__CLASS__] = 'Fake frontend';

                    return;
                }
            }
        }

        if ($request->hasHeader('x-yoast-page-request')) {
            $skipProcessing = true;
            $explanation[__CLASS__] = 'Yoast SEO page request';
        }
    }

    /**
     * Get all call paths.
     */
    protected function getCallPaths(): array
    {
        $paths = [];
        $backTrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        foreach ($backTrace as $value) {
            if (isset($value['file'])) {
                $paths[] = $value['file'];
            }
        }

        return $paths;
    }
}

<?php

/**
 * ValidRequestMethod.
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Cache\Rule;

use Psr\Http\Message\ServerRequestInterface;

/**
 * ValidRequestMethod.
 */
class ValidRequestMethod extends AbstractRule
{
    /**
     * Method to check the rul and modify $explanation and/or $skipProcessing.
     *
     *
     * @param ServerRequestInterface $request
     * @param array                        $explanation
     * @param bool                         $skipProcessing
     */
    public function checkRule(ServerRequestInterface $request, array &$explanation, bool &$skipProcessing): void
    {
        if ('GET' !== $request->getMethod()) {
            $explanation[__CLASS__] = 'The request methode has to be GET';
            $skipProcessing = true;
        }
    }
}

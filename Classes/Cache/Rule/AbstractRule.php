<?php

/**
 * Abstract Rule.
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Cache\Rule;

use Psr\Http\Message\ServerRequestInterface;
use SFC\Staticfilecache\StaticFileCacheObject;

/**
 * Abstract Rule.
 */
abstract class AbstractRule extends StaticFileCacheObject
{
    /**
     * Method to check the rule and modify $explanation and/or $skipProcessing.
     */
    abstract public function checkRule(ServerRequestInterface $request, array &$explanation, bool &$skipProcessing): void;
}

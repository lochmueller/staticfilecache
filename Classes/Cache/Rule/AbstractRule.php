<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Cache\Rule;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Abstract Rule.
 * @todo migrate to Listener
 */
abstract class AbstractRule
{
    /**
     * Method to check the rule and modify $explanation and/or $skipProcessing.
     */
    abstract public function checkRule(ServerRequestInterface $request, array &$explanation, bool &$skipProcessing): void;
}

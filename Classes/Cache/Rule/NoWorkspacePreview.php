<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Cache\Rule;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Context\Context;

/**
 * No workspace preview.
 */
class NoWorkspacePreview extends AbstractRule
{
    public function __construct(
        private readonly Context $context,
    ) {}

    /**
     * Check if it is no workspace preview.
     */
    public function checkRule(ServerRequestInterface $request, array &$explanation, bool &$skipProcessing): void
    {
        if ($this->context->getPropertyFromAspect('workspace', 'isOffline', false)) {
            $explanation[__CLASS__] = 'The page is in workspace preview mode';
        }
    }
}

<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Cache\Listener;

use Psr\Http\Message\ServerRequestInterface;
use SFC\Staticfilecache\Event\CacheRuleEvent;
use TYPO3\CMS\Core\Context\Context;

class NoWorkspacePreviewListener
{
    public function __construct(
        private readonly Context $context,
    ) {}

    /**
     * Check if it is no workspace preview.
     */
    public function __invoke(CacheRuleEvent $event): void
    {
        if ($this->context->getPropertyFromAspect('workspace', 'isOffline', false)) {
            $event->addExplanation(__CLASS__, 'The page is in workspace preview mode');
        }
    }
}

<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Cache\Listener;

use SFC\Staticfilecache\Event\CacheRuleEvent;
use TYPO3\CMS\Core\Context\Context;

/**
 * No active BE user.
 */
class NoBackendUserListener
{
    public function __construct(private readonly Context $context) {}

    public function __invoke(CacheRuleEvent $event): void
    {
        if ($this->context->getPropertyFromAspect('backend.user', 'isLoggedIn', false)) {
            $event->addExplanation(__CLASS__, 'Active BE Login (TSFE:beUserLogin)');
            $event->setSkipProcessing(true);
        }
    }
}

<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Cache\Listener;

use SFC\Staticfilecache\Event\CacheRuleEvent;
use TYPO3\CMS\Core\Context\Context;

class NoUserOrGroupSetListener
{
    public function __construct(protected readonly Context $context) {}

    public function __invoke(CacheRuleEvent $event): void
    {
        if ($this->context->getAspect('frontend.user')->isUserOrGroupSet()) {
            $event->addExplanation(__CLASS__, 'User or group are set');
        }
    }
}

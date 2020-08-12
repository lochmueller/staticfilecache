<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\EventListener\CacheRule;

use SFC\Staticfilecache\Event\CacheRuleEvent;

/**
 * ValidRequestMethod.
 */
class ValidRequestMethodListener
{
    public function __invoke(CacheRuleEvent $event): void
    {
        if ('GET' !== $event->getRequest()->getMethod()) {
            $event->addExplanation(__CLASS__, 'The request methode has to be GET');
            $event->setSkipProcessing(true);
        }
    }
}

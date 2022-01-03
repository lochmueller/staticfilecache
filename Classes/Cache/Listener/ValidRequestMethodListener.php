<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Cache\Listener;

use SFC\Staticfilecache\Event\CacheRuleEventInterface;

/**
 * ValidRequestMethod.
 */
class ValidRequestMethodListener
{
    public function __invoke(CacheRuleEventInterface $event): void
    {
        if ('GET' !== $event->getRequest()->getMethod()) {
            $event->addExplanation(__CLASS__, 'The request methode has to be GET');
            $event->setSkipProcessing(true);
        }
    }
}

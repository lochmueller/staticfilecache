<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Cache\Listener;

use SFC\Staticfilecache\Event\CacheRuleEventInterface;

/**
 * Check if the authentication header exists
 */
class NoAuthorizationListener
{
    public function __invoke(CacheRuleEventInterface $event): void
    {
        if ($event->getRequest()->getHeaderLine('Authorization') !== '') {
            $event->addExplanation(__CLASS__, 'Auth request');
            $event->setSkipProcessing(true);
        }
    }
}

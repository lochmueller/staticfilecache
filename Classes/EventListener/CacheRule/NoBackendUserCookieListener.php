<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\EventListener\CacheRule;

use SFC\Staticfilecache\Event\CacheRuleEvent;

/**
 * No active BE user (just check the cookie).
 */
class NoBackendUserCookieListener
{
    public function __invoke(CacheRuleEvent $event): void
    {
        if (isset($_COOKIE[$GLOBALS['TYPO3_CONF_VARS']['BE']['cookieName']])) {
            $event->setSkipProcessing(true);
            $event->addExplanation(__CLASS__, 'BE Login Cookie');
        }
    }
}

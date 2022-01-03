<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Cache\Listener;

use SFC\Staticfilecache\Event\CacheRuleEventInterface;

/**
 * No active BE user (just check the cookie).
 */
class NoBackendUserCookieListener
{
    public function __invoke(CacheRuleEventInterface $event): void
    {
        if (isset($_COOKIE[$GLOBALS['TYPO3_CONF_VARS']['BE']['cookieName']])) {
            $event->setSkipProcessing(true);
            $event->addExplanation(__CLASS__, 'BE Login Cookie');
        }
    }
}

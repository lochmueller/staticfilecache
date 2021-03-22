<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Cache\Listener;

use SFC\Staticfilecache\Event\CacheRuleEvent;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * No active BE user.
 */
class NoBackendUserListener
{
    public function __invoke(CacheRuleEvent $event): void
    {
        $context = GeneralUtility::makeInstance(Context::class);
        if ($context->getPropertyFromAspect('backend.user', 'isLoggedIn', false)) {
            $event->addExplanation(__CLASS__, 'Active BE Login (TSFE:beUserLogin)');
            $event->setSkipProcessing(true);
        }
    }
}

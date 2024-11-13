<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Cache\Listener;

use SFC\Staticfilecache\Event\CacheRuleEvent;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class NoUserOrGroupSetListener
{
    /**
     * Check if no user or group is set.
     */
    public function __invoke(CacheRuleEvent $event): void
    {
        $context = GeneralUtility::makeInstance(Context::class);
        if ($context->getAspect('frontend.user')->isUserOrGroupSet()) {
            $event->addExplanation(__CLASS__, 'User or group are set');
        }
    }
}

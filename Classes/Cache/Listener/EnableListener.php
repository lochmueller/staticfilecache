<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Cache\Listener;

use SFC\Staticfilecache\Event\CacheRuleEvent;
use SFC\Staticfilecache\Service\ConfigurationService;

class EnableListener
{
    public function __construct(protected readonly ConfigurationService $configurationService) {}
    public function __invoke(CacheRuleEvent $event): void
    {
        if ($this->configurationService->isBool('disableCache')) {
            $event->addExplanation(__CLASS__, 'static cache disabled by TypoScript');
        }
    }
}

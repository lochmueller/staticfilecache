<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Cache\Listener;

use SFC\Staticfilecache\Event\CacheRuleEvent;

class ValidResponseCodeListener
{
    public function __invoke(CacheRuleEvent $event): void
    {
        $validStatusCodes = [200];
        $responseStatusCode = $event->getResponse()->getStatusCode();

        if (!in_array($responseStatusCode, $validStatusCodes)) {
            $event->addExplanation(__CLASS__, 'The Status code ist not valid');
            $event->setSkipProcessing(true);
        }
    }
}

<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Cache\Listener;

use SFC\Staticfilecache\Event\CacheRuleEvent;

/**
 * Check if there is no path segment that is to long.
 */
class NoLongPathSegmentListener
{
    /**
     * Check if there is no path segment that is to long.
     */
    public function __invoke(CacheRuleEvent $event): void
    {
        $uri = (string) $event->getRequest()->getUri();
        $path = (string) parse_url($uri, PHP_URL_PATH);
        $segments = explode('/', $path);

        foreach ($segments as $segment) {
            if (\strlen($segment) > 255) {
                $event->addExplanation(__CLASS__, 'The URI seegment of the URI is to long to create a folder based on tthis segment: ' . $segment);
                $event->setSkipProcessing(true);

                return;
            }
        }

    }
}

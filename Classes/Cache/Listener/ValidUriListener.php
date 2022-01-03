<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Cache\Listener;

use SFC\Staticfilecache\Event\CacheRuleEventInterface;

/**
 * Check if the URI is valid
 * Note: A "valid URL" check is already done in the URI frontend.
 */
class ValidUriListener
{
    public function __invoke(CacheRuleEventInterface $event): void
    {
        $uri = (string) $event->getRequest()->getUri();
        if (false !== mb_strpos($uri, '?')) {
            $event->addExplanation(__CLASS__, 'The URI contain a "?" that is not allowed for StaticFileCache');
            $event->setSkipProcessing(true);
        } elseif (false !== mb_strpos($uri, 'index.php')) {
            $event->addExplanation(__CLASS__, 'The URI contain a "index.php" that is not allowed for StaticFileCache');
            $event->setSkipProcessing(true);
        } elseif (false !== mb_strpos(parse_url($uri, PHP_URL_PATH), '//')) {
            $event->addExplanation(__CLASS__, 'Illegal link configuration. The URI should not contain a "//" '.
                'because a folder name without name is not possible');
            $event->setSkipProcessing(true);
        }
    }
}

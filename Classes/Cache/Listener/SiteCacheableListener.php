<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Cache\Listener;

use SFC\Staticfilecache\Event\CacheRuleEvent;
use TYPO3\CMS\Core\Site\Entity\Site;

class SiteCacheableListener
{
    /**
     * Check if the current site is static cacheable.
     */
    public function __invoke(CacheRuleEvent $event): void
    {
        $site = $event->getRequest()->getAttribute('site');
        if (!($site instanceof Site)) {
            return;
        }
        $config = $site->getConfiguration();
        if (isset($config['disableStaticFileCache']) && $config['disableStaticFileCache']) {
            $event->addExplanation(__CLASS__, 'static cache disabled on site configuration: ' . $site->getIdentifier());
        }
    }
}

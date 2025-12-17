<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Cache\Listener;

use SFC\Staticfilecache\Event\CacheRuleEvent;
use TYPO3\CMS\Frontend\Page\PageInformation;

/**
 * ValidPageInformation.
 *
 * @see https://github.com/lochmueller/staticfilecache/issues/150
 */
class ValidPageInformationListener
{
    /**
     * ValidPageInformation.
     */
    public function __invoke(CacheRuleEvent $event): void
    {
        $pageInformation = $event->getRequest()->getAttribute('frontend.page.information');
        if (!$pageInformation instanceof PageInformation) {
            $event->setSkipProcessing(true);
            $event->addExplanation(__CLASS__, 'There is no valid page information in the frontend.page.information');
        }
    }
}

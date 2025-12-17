<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Cache\Listener;

use SFC\Staticfilecache\Event\CacheRuleEvent;
use TYPO3\CMS\Frontend\Page\PageInformation;

class ValidDoktypeListener
{
    /**
     * Check if the doktype is valid.
     */
    public function __invoke(CacheRuleEvent $event): void
    {

        $pageInformation = $event->getRequest()->getAttribute('frontend.page.information');
        if (!$pageInformation instanceof PageInformation) {
            $event->addExplanation(__CLASS__, 'There is no valid page in the frontend.page.information');
            $event->setSkipProcessing(true);

            return;
        }

        $ignoreTypes = [
            3, // DOKTYPE_LINK,
            254, // DOKTYPE_SYSFOLDER,
            255, // DOKTYPE_RECYCLER,
        ];

        $currentType = (int) ($pageInformation->getPageRecord()['doktype'] ?? 1);
        if (\in_array($currentType, $ignoreTypes, true)) {

            $event->addExplanation(__CLASS__, 'The Page doktype ' . $currentType . ' is one of the following not allowed numbers: ' . implode(
                ', ',
                $ignoreTypes
            ));
            $event->setSkipProcessing(true);
        }
    }
}

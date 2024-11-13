<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Cache\Listener;

use SFC\Staticfilecache\Event\CacheRuleEvent;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class ValidDoktypeListener
{
    /**
     * Check if the doktype is valid.
     */
    public function __invoke(CacheRuleEvent $event): void
    {
        $tsfe = $GLOBALS['TSFE'] ?? null;
        if (!($tsfe instanceof TypoScriptFrontendController) || !isset($GLOBALS['TSFE']->page)) {
            $event->addExplanation(__CLASS__, 'There is no valid page in the frontendController object');
            $event->setSkipProcessing(true);

            return;
        }

        $ignoreTypes = [
            3, // DOKTYPE_LINK,
            254, // DOKTYPE_SYSFOLDER,
            255, // DOKTYPE_RECYCLER,
        ];

        $currentType = (int) ($GLOBALS['TSFE']->page['doktype'] ?? 1);
        if (\in_array($currentType, $ignoreTypes, true)) {

            $event->addExplanation(__CLASS__, 'The Page doktype ' . $currentType . ' is one of the following not allowed numbers: ' . implode(
                ', ',
                $ignoreTypes
            ));
            $event->setSkipProcessing(true);
        }
    }
}

<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Cache\Listener;

use Psr\Http\Message\ServerRequestInterface;
use SFC\Staticfilecache\Event\CacheRuleEvent;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

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
        $tsfe = $GLOBALS['TSFE'] ?? null;
        if (!$tsfe instanceof TypoScriptFrontendController || !\is_array($tsfe->page) || !$tsfe->page['uid']) {
            $event->setSkipProcessing(true);
            $event->addExplanation(__CLASS__, 'There is no valid page in the TSFE');
        }
    }
}

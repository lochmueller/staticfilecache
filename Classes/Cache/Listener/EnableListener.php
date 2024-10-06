<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Cache\Listener;

use Psr\Http\Message\ServerRequestInterface;
use SFC\Staticfilecache\Event\CacheRuleEvent;
use SFC\Staticfilecache\Service\ConfigurationService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class EnableListener
{
    public function __invoke(CacheRuleEvent $event): void
    {
        /** @var ConfigurationService $configuration */
        $configuration = GeneralUtility::makeInstance(ConfigurationService::class);
        if ($configuration->isBool('disableCache')) {
            $event->addExplanation(__CLASS__, 'static cache disabled by TypoScript');
        }
    }
}

<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Cache\Listener;

use SFC\Staticfilecache\Event\CacheRuleEvent;

class NoFakeFrontendListener
{
    public function __invoke(CacheRuleEvent $event): void
    {
        $ignorePaths = [
            // Solr extension
            '/solr/Classes/Eid/Suggest.php',
            '/solr/Classes/Util.php',
        ];
        foreach ($ignorePaths as $ignorePath) {
            foreach ($this->getCallPaths() as $path) {
                if (str_ends_with($path, $ignorePath)) {
                    $event->setSkipProcessing(true);
                    $event->addExplanation(__CLASS__, 'Fake frontend');
                    return;
                }
            }
        }

        if ($event->getRequest()->hasHeader('x-yoast-page-request')) {
            $event->setSkipProcessing(true);
            $event->addExplanation(__CLASS__, 'Yoast SEO page request');
        }
    }

    /**
     * Get all call paths.
     */
    protected function getCallPaths(): array
    {
        $paths = [];
        $backTrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        foreach ($backTrace as $value) {
            if (isset($value['file'])) {
                $paths[] = $value['file'];
            }
        }

        return $paths;
    }
}

<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Cache\Listener;

use SFC\Staticfilecache\Cache\Listener\NoFakeFrontendListener;
use TYPO3\CMS\Core\Http\ServerRequest;

class NoFakeFrontendListenerTest extends AbstractListenerTest
{
    public function testNoExplanation(): void
    {
        $listener = new NoFakeFrontendListener();

        $event = $this->emptyCacheRuleEvent();
        $listener($event);

        self::assertEquals([], $event->getExplanation());
        self::assertEquals(false, $event->isSkipProcessing());
    }

    public function testExplanationAndSkipForYoastRequest(): void
    {
        $listener = new NoFakeFrontendListener();

        $event = $this->cacheRuleEvent((new ServerRequest())->withHeader('x-yoast-page-request', '1'));
        $listener($event);

        self::assertEquals([NoFakeFrontendListener::class => 'Yoast SEO page request'], $event->getExplanation());
        self::assertEquals(true, $event->isSkipProcessing());
    }

    public function testExplanationAndSkipForSolrCallPath(): void
    {
        $listener = new class extends NoFakeFrontendListener {
            protected function getCallPaths(): array
            {
                return ['/var/www/vendor/apache-solr-for-typo3/solr/Classes/Util.php'];
            }
        };

        $event = $this->emptyCacheRuleEvent();
        $listener($event);

        self::assertEquals([NoFakeFrontendListener::class => 'Fake frontend'], $event->getExplanation());
        self::assertEquals(true, $event->isSkipProcessing());
    }
}

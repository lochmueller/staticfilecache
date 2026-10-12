<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Event;

use SFC\Staticfilecache\Event\ForceStaticFileCacheEvent;
use SFC\Staticfilecache\Tests\Unit\AbstractTest;
use TYPO3\CMS\Core\Http\ServerRequest;

/**
 * ForceStaticFileCacheEventTest.
 *
 * @internal
 * @coversNothing
 */
final class ForceStaticFileCacheEventTest extends AbstractTest
{
    public function testGetAndSetForceStatic(): void
    {
        $event = new ForceStaticFileCacheEvent(false, new ServerRequest('https://example.com/'));
        self::assertFalse($event->isForceStatic());

        $event->setForceStatic(true);
        self::assertTrue($event->isForceStatic());
    }

    public function testGetAndSetRequest(): void
    {
        $request = new ServerRequest('https://example.com/');
        $event = new ForceStaticFileCacheEvent(false, $request);
        self::assertSame($request, $event->getRequest());

        $otherRequest = $request->withAttribute('test', true);
        $event->setRequest($otherRequest);
        self::assertSame($otherRequest, $event->getRequest());
    }
}

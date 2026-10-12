<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Service;

use Psr\EventDispatcher\EventDispatcherInterface;
use SFC\Staticfilecache\Event\HttpPushHeaderEvent;
use SFC\Staticfilecache\Service\HttpPushService;
use SFC\Staticfilecache\Tests\Unit\AbstractTest;
use TYPO3\CMS\Core\Http\ServerRequest;

/**
 * HttpPushServiceTest.
 *
 * @internal
 * @coversNothing
 */
final class HttpPushServiceTest extends AbstractTest
{
    public function testReturnEmptyHeadersIfDisabled(): void
    {
        $this->setExtensionConfiguration(['sendHttp2PushEnable' => '0']);

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::never())->method('dispatch');

        self::assertSame([], (new HttpPushService($eventDispatcher))->getHttpPushHeaders(new ServerRequest(), '<html></html>'));
    }

    public function testDispatchEventAndLimitHeaders(): void
    {
        $this->setExtensionConfiguration([
            'sendHttp2PushEnable' => '1',
            'sendHttp2PushFileLimit' => '2',
            'sendHttp2PushFileExtensions' => 'css, js',
        ]);

        $dispatchedEvent = null;
        $eventDispatcher = $this->createStub(EventDispatcherInterface::class);
        $eventDispatcher->method('dispatch')->willReturnCallback(static function (HttpPushHeaderEvent $event) use (&$dispatchedEvent): object {
            $dispatchedEvent = $event;
            $event->setHeaders([
                ['path' => '/a.css', 'type' => 'style'],
                ['path' => '/b.js', 'type' => 'script'],
                ['path' => '/c.js', 'type' => 'script'],
            ]);

            return $event;
        });

        $headers = (new HttpPushService($eventDispatcher))->getHttpPushHeaders(new ServerRequest(), '<html>content</html>');

        self::assertSame([
            ['path' => '/a.css', 'type' => 'style'],
            ['path' => '/b.js', 'type' => 'script'],
        ], $headers);
        self::assertInstanceOf(HttpPushHeaderEvent::class, $dispatchedEvent);
        self::assertSame(['css', 'js'], $dispatchedEvent->getExtensions());
        self::assertSame('<html>content</html>', $dispatchedEvent->getContent());
    }

    public function testLimitContentToArea(): void
    {
        $this->setExtensionConfiguration([
            'sendHttp2PushEnable' => '1',
            'sendHttp2PushFileLimit' => '10',
            'sendHttp2PushLimitToArea' => 'head',
            'sendHttp2PushFileExtensions' => 'css',
        ]);

        $content = null;
        $eventDispatcher = $this->createStub(EventDispatcherInterface::class);
        $eventDispatcher->method('dispatch')->willReturnCallback(static function (HttpPushHeaderEvent $event) use (&$content): object {
            $content = $event->getContent();

            return $event;
        });

        (new HttpPushService($eventDispatcher))->getHttpPushHeaders(new ServerRequest(), '<html><head><link href="/a.css"></head><body>body</body></html>');

        self::assertSame('<head><link href="/a.css">', $content);
    }
}

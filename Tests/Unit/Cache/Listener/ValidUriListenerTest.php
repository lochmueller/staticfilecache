<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Cache\Listener;

use PHPUnit\Framework\Attributes\DataProvider;
use SFC\Staticfilecache\Cache\Listener\ValidUriListener;
use TYPO3\CMS\Core\Http\ServerRequest;

class ValidUriListenerTest extends AbstractListenerTest
{
    public function testNoExplanation(): void
    {
        $listener = new ValidUriListener();

        $event = $this->cacheRuleEvent(new ServerRequest('https://example.com/page/subpage/'));
        $listener($event);

        self::assertEquals([], $event->getExplanation());
        self::assertEquals(false, $event->isSkipProcessing());
    }

    #[DataProvider('invalidUriProvider')]
    public function testExplanationAndSkip(string $uri, string $expectedPart): void
    {
        $listener = new ValidUriListener();

        $event = $this->cacheRuleEvent(new ServerRequest($uri));
        $listener($event);

        self::assertArrayHasKey(ValidUriListener::class, $event->getExplanation());
        self::assertStringContainsString($expectedPart, $event->getExplanation()[ValidUriListener::class]);
        self::assertEquals(true, $event->isSkipProcessing());
    }

    public static function invalidUriProvider(): \Generator
    {
        yield 'query string' => ['https://example.com/page/?foo=bar', '"?"'];
        yield 'index.php' => ['https://example.com/index.php', '"index.php"'];
        yield 'double slash' => ['https://example.com/page//subpage/', '"//"'];
    }
}

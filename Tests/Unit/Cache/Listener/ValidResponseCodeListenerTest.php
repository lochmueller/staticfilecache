<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Cache\Listener;

use PHPUnit\Framework\Attributes\DataProvider;
use SFC\Staticfilecache\Cache\Listener\ValidResponseCodeListener;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\ServerRequest;

class ValidResponseCodeListenerTest extends AbstractListenerTest
{
    public function testNoExplanation(): void
    {
        $listener = new ValidResponseCodeListener();

        $event = $this->cacheRuleEvent(new ServerRequest(), new Response(null, 200));
        $listener($event);

        self::assertEquals([], $event->getExplanation());
        self::assertEquals(false, $event->isSkipProcessing());
    }

    #[DataProvider('invalidStatusCodeProvider')]
    public function testExplanationAndSkip(int $statusCode): void
    {
        $listener = new ValidResponseCodeListener();

        $event = $this->cacheRuleEvent(new ServerRequest(), new Response(null, $statusCode));
        $listener($event);

        self::assertArrayHasKey(ValidResponseCodeListener::class, $event->getExplanation());
        self::assertEquals(true, $event->isSkipProcessing());
    }

    public static function invalidStatusCodeProvider(): \Generator
    {
        yield 'created' => [201];
        yield 'moved permanently' => [301];
        yield 'not found' => [404];
        yield 'server error' => [500];
    }
}

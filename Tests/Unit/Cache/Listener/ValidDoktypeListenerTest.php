<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Cache\Listener;

use PHPUnit\Framework\Attributes\DataProvider;
use SFC\Staticfilecache\Cache\Listener\ValidDoktypeListener;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Frontend\Page\PageInformation;

class ValidDoktypeListenerTest extends AbstractListenerTest
{
    public function testExplanationAndSkipWithoutPageInformation(): void
    {
        $listener = new ValidDoktypeListener();

        $event = $this->emptyCacheRuleEvent();
        $listener($event);

        self::assertArrayHasKey(ValidDoktypeListener::class, $event->getExplanation());
        self::assertEquals(true, $event->isSkipProcessing());
    }

    /**
     * @param array<string, int> $pageRecord
     */
    #[DataProvider('validDoktypeProvider')]
    public function testNoExplanation(array $pageRecord): void
    {
        $listener = new ValidDoktypeListener();

        $event = $this->cacheRuleEvent($this->createRequest($pageRecord));
        $listener($event);

        self::assertEquals([], $event->getExplanation());
        self::assertEquals(false, $event->isSkipProcessing());
    }

    /**
     * @return \Generator<string, array{array<string, int>}>
     */
    public static function validDoktypeProvider(): \Generator
    {
        yield 'no doktype' => [[]];
        yield 'standard' => [['doktype' => 1]];
        yield 'shortcut' => [['doktype' => 4]];
    }

    #[DataProvider('invalidDoktypeProvider')]
    public function testExplanationAndSkip(int $doktype): void
    {
        $listener = new ValidDoktypeListener();

        $event = $this->cacheRuleEvent($this->createRequest(['doktype' => $doktype]));
        $listener($event);

        self::assertArrayHasKey(ValidDoktypeListener::class, $event->getExplanation());
        self::assertEquals(true, $event->isSkipProcessing());
    }

    /**
     * @return \Generator<string, array{int}>
     */
    public static function invalidDoktypeProvider(): \Generator
    {
        yield 'link' => [3];
        yield 'sysfolder' => [254];
        yield 'recycler' => [255];
    }

    /**
     * @param array<string, mixed> $pageRecord
     */
    protected function createRequest(array $pageRecord): ServerRequest
    {
        $pageInformation = new PageInformation();
        $pageInformation->setPageRecord($pageRecord);

        return (new ServerRequest())->withAttribute('frontend.page.information', $pageInformation);
    }
}

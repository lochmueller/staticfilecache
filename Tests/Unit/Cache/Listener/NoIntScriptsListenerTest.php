<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Cache\Listener;

use SFC\Staticfilecache\Cache\Listener\NoIntScriptsListener;
use SFC\Staticfilecache\Service\ConfigurationService;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Frontend\Cache\NonceValueSubstitution;
use TYPO3\CMS\Frontend\Page\PageParts;

class NoIntScriptsListenerTest extends AbstractListenerTest
{
    public function testNoExplanationWithoutPageParts(): void
    {
        $listener = new NoIntScriptsListener($this->createConfigurationService(false));

        $event = $this->emptyCacheRuleEvent();
        $listener($event);

        self::assertEquals([], $event->getExplanation());
        self::assertEquals(false, $event->isSkipProcessing());
    }

    public function testNoExplanationWithoutNotCachedElements(): void
    {
        $listener = new NoIntScriptsListener($this->createConfigurationService(false));

        $event = $this->cacheRuleEvent((new ServerRequest())->withAttribute('frontend.page.parts', new PageParts()));
        $listener($event);

        self::assertEquals([], $event->getExplanation());
        self::assertEquals(false, $event->isSkipProcessing());
    }

    public function testExplanationForNotCachedElement(): void
    {
        $pageParts = new PageParts();
        $pageParts->addNotCachedContentElement([
            'substKey' => 'INT_SCRIPT.abc',
            'type' => 'COA',
            'conf' => [
                'userFunc' => 'Vendor\\Ext\\Plugin->main',
                'extensionName' => 'Ext',
                'pluginName' => 'Plugin',
            ],
        ]);

        $listener = new NoIntScriptsListener($this->createConfigurationService(false));

        $event = $this->cacheRuleEvent((new ServerRequest())->withAttribute('frontend.page.parts', $pageParts));
        $listener($event);

        $explanation = $event->getExplanation();
        self::assertCount(1, $explanation);
        $message = (string) reset($explanation);
        self::assertStringStartsWith(NoIntScriptsListener::class . ':', (string) key($explanation));
        self::assertStringContainsString('substKey: INT_SCRIPT.abc', $message);
        self::assertStringContainsString('type: COA', $message);
        self::assertStringContainsString('userFunc: Vendor\\Ext\\Plugin->main', $message);
        self::assertStringContainsString('extensionName: Ext', $message);
        self::assertStringContainsString('pluginName: Plugin', $message);
        self::assertEquals(false, $event->isSkipProcessing());
    }

    public function testNonceElementIsIgnoredWithCspGenerationOverride(): void
    {
        $listener = new NoIntScriptsListener($this->createConfigurationService(true));

        $event = $this->cacheRuleEvent((new ServerRequest())->withAttribute('frontend.page.parts', $this->createNoncePageParts()));
        $listener($event);

        self::assertEquals([], $event->getExplanation());
    }

    public function testNonceElementIsReportedWithoutCspGenerationOverride(): void
    {
        $listener = new NoIntScriptsListener($this->createConfigurationService(false));

        $event = $this->cacheRuleEvent((new ServerRequest())->withAttribute('frontend.page.parts', $this->createNoncePageParts()));
        $listener($event);

        self::assertCount(1, $event->getExplanation());
    }

    protected function createNoncePageParts(): PageParts
    {
        $pageParts = new PageParts();
        $pageParts->addNotCachedContentElement([
            'target' => NonceValueSubstitution::class . '->substituteNonce',
        ]);

        return $pageParts;
    }

    protected function createConfigurationService(bool $cspGenerationOverride): ConfigurationService
    {
        $configurationService = $this->createStub(ConfigurationService::class);
        $configurationService->method('get')->willReturnMap([
            ['cspGenerationOverride', $cspGenerationOverride ? '1' : '0'],
        ]);

        return $configurationService;
    }
}

<?php

/**
 * Test the valid URI Rule.
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\EventListener\CacheRule;

use SFC\Staticfilecache\Event\CacheRuleEvent;
use SFC\Staticfilecache\EventListener\CacheRule\ValidUriListener;
use SFC\Staticfilecache\Tests\Unit\AbstractTest;
use TYPO3\CMS\Core\Http\ServerRequest;

/**
 * Test the valid URI Rule.
 *
 * @internal
 * @coversNothing
 */
class ValidUriListenerTest extends AbstractTest
{
    public function testInvalidUri()
    {
        $validUriRule = new ValidUriListener();

        $events = [
            new CacheRuleEvent(new ServerRequest('/index.php'), [], false),
            new CacheRuleEvent(new ServerRequest('/?param=value'), [], false),
            new CacheRuleEvent(new ServerRequest('/?type=1533906435'), [], false),
            new CacheRuleEvent(new ServerRequest('/invalid//path'), [], false),
        ];
        foreach ($events as $event) {
            $validUriRule($event);
            self::assertTrue($event->isSkipProcessing(), 'Is "' . $event->getRequest()->getUri() . '" valid?');
        }
    }

    public function testValidUri()
    {
        $validUriRule = new ValidUriListener();

        $events = [
            new CacheRuleEvent(new ServerRequest(''), [], false),
            new CacheRuleEvent(new ServerRequest('/'), [], false),
            new CacheRuleEvent(new ServerRequest('/home.html'), [], false),
            new CacheRuleEvent(new ServerRequest('/home.jsp'), [], false),
            new CacheRuleEvent(new ServerRequest('/home/deep'), [], false),
            new CacheRuleEvent(new ServerRequest('/home/deep.html'), [], false),
        ];
        foreach ($events as $event) {
            $validUriRule($event);
            self::assertFalse($event->isSkipProcessing(), 'Is "' . $event->getRequest()->getUri() . '" valid?');
        }
    }
}

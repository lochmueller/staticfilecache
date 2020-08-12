<?php

/**
 * Test the valid URI Rule.
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Cache\Rule;

use SFC\Staticfilecache\Cache\Rule\ValidUriListener;
use TYPO3\CMS\Core\Http\ServerRequest;

/**
 * Test the valid URI Rule.
 *
 * @internal
 * @coversNothing
 */
class ValidUriTest extends AbstractRuleTest
{
    public function testInvalidUri()
    {
        $explanation = [];

        $validUriRule = new ValidUriListener();

        $requests = [
            new ServerRequest('/index.php'),
            new ServerRequest('/?param=value'),
            new ServerRequest('/?type=1533906435'),
            new ServerRequest('/invalid//path'),
        ];
        foreach ($requests as $request) {
            $skipProcessing = false;
            $validUriRule->checkRule($request, $explanation, $skipProcessing);
            self::assertTrue($skipProcessing, 'Is "' . $request->getUri() . '" valid?');
        }
    }

    public function testValidUri()
    {
        $explanation = [];

        $validUriRule = new ValidUriListener();

        $requests = [
            new ServerRequest(''),
            new ServerRequest('/'),
            new ServerRequest('/home.html'),
            new ServerRequest('/home.jsp'),
            new ServerRequest('/home/deep'),
            new ServerRequest('/home/deep.html'),
        ];
        foreach ($requests as $request) {
            $skipProcessing = false;
            $validUriRule->checkRule($request, $explanation, $skipProcessing);
            self::assertFalse($skipProcessing, 'Is "' . $request->getUri() . '" valid?');
        }
    }
}

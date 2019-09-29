<?php

/**
 * Test the valid URI Rule.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Tests\Unit\Cache\Rule;

use SFC\Staticfilecache\Cache\Rule\ValidUri;
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
        $tsfe = $this->getTsfe();
        $explanation = [];
        $skipProcessing = false;

        $validUriRule = new ValidUri();

        $requests = [
            new ServerRequest('/index.php'),
            new ServerRequest('/?param=value'),
            new ServerRequest('/?type=1533906435'),
            new ServerRequest('/invalid//path'),
        ];
        foreach ($requests as $request) {
            $result = $validUriRule->check($tsfe, $request, $explanation, $skipProcessing);
            $this->assertTrue($result['skipProcessing'], 'Is "' . $request->getUri() . '" valid?');
        }
    }

    public function testValidUri()
    {
        $tsfe = $this->getTsfe();
        $explanation = [];
        $skipProcessing = false;

        $validUriRule = new ValidUri();

        $requests = [
            new ServerRequest(''),
            new ServerRequest('/'),
            new ServerRequest('/home.html'),
            new ServerRequest('/home.jsp'),
            new ServerRequest('/home/deep'),
            new ServerRequest('/home/deep.html'),
        ];
        foreach ($requests as $request) {
            $result = $validUriRule->check($tsfe, $request, $explanation, $skipProcessing);
            $this->assertFalse($result['skipProcessing'], 'Is "' . $request->getUri() . '" valid?');
        }
    }
}

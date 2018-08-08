<?php

/**
 * Test the valid URI Rule.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Tests\Unit\Cache\Rule;

use SFC\Staticfilecache\Cache\Rule\ValidUri;

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
        $paths = [
            '/index.php',
            '/?param=value',
            '/invalid//path',
        ];
        foreach ($paths as $path) {
            $result = $validUriRule->check($tsfe, $path, $explanation, $skipProcessing);
            $this->assertTrue($result['skipProcessing'], 'Is "' . $path . '" valid?');
        }
    }

    public function testValidUri()
    {
        $tsfe = $this->getTsfe();
        $explanation = [];
        $skipProcessing = false;

        $validUriRule = new ValidUri();
        $paths = [
            '',
            '/home.html',
            '/home.jsp',
            '/home/deep',
            '/home/deep.html',
        ];
        foreach ($paths as $path) {
            $result = $validUriRule->check($tsfe, $path, $explanation, $skipProcessing);
            $this->assertFalse($result['skipProcessing'], 'Is "' . $path . '" valid?');
        }
    }
}

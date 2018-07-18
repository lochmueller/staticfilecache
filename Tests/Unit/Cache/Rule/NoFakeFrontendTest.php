<?php

/**
 * Test the Fake Frontend Rule.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Tests\Unit\Cache\Rule;

use SFC\Staticfilecache\Cache\Rule\NoFakeFrontend;

/**
 * Test the Fake Frontend Rule.
 */
class NoFakeFrontendTest extends AbstractRuleTest
{
    /**
     * @test
     */
    public function checkNoFakeFrontendController()
    {
        $tsfe = $this->getTsfe();
        $uri = '';
        $explanation = [];
        $skipProcessing = false;

        $fakeFrontendRule = new NoFakeFrontend();
        $result = $fakeFrontendRule->check($tsfe, $uri, $explanation, $skipProcessing);
        $this->assertFalse($result['skipProcessing']);
    }
}

<?php

/**
 * Test the Fake Frontend Rule.
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Cache\Rule;

use SFC\Staticfilecache\Cache\Rule\NoFakeFrontend;
use TYPO3\CMS\Core\Http\ServerRequest;

/**
 * Test the Fake Frontend Rule.
 *
 * @internal
 * @coversNothing
 */
final class NoFakeFrontendTest extends AbstractRuleTest
{
    public function testCheckNoFakeFrontendController(): void
    {
        $request = new ServerRequest();
        $explanation = [];
        $skipProcessing = false;

        $fakeFrontendRule = new NoFakeFrontend();
        $fakeFrontendRule->checkRule($request, $explanation, $skipProcessing);
        static::assertFalse($skipProcessing);
    }
}

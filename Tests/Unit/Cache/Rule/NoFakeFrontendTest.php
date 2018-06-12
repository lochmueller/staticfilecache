<?php

declare(strict_types = 1);
/**
 * Test the Fake Frontend Rule.
 */
namespace SFC\Staticfilecache\Tests\Unit\Cache\Rule;

/**
 * Test the Fake Frontend Rule.
 */
class NoFakeFrontendTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function checkValidPath()
    {
        $tsfe = new \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController([], 0, 0);
        $uri = '';
        $explanation = [];
        $skipProcessing = false;

        $fakeFrontendRule = new \SFC\Staticfilecache\Cache\Rule\NoFakeFrontend();
        $result = $fakeFrontendRule->check($tsfe, $uri, $explanation, $skipProcessing);
        $this->assertFalse($result['skipProcessing']);
    }
}

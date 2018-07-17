<?php

/**
 * Test the Fake Frontend Rule.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Tests\Unit\Cache\Rule;

use SFC\Staticfilecache\Cache\Rule\NoFakeFrontend;
use TYPO3\CMS\Core\Cache\Backend\NullBackend;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Test the Fake Frontend Rule.
 */
class NoFakeFrontendTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function checkNoFakeFrontendController()
    {
        // Init the cache_pages cache, to avoid exceptions in thes TSFE building process
        $cacheManager = GeneralUtility::makeInstance(CacheManager::class);
        $cacheManager->setCacheConfigurations([
            'cache_pages' => [
                'frontend' => VariableFrontend::class,
                'backend' => NullBackend::class,
            ],
        ]);

        $tsfe = new TypoScriptFrontendController([], 0, 0);
        $uri = '';
        $explanation = [];
        $skipProcessing = false;

        $fakeFrontendRule = new NoFakeFrontend();
        $result = $fakeFrontendRule->check($tsfe, $uri, $explanation, $skipProcessing);
        $this->assertFalse($result['skipProcessing']);
    }
}

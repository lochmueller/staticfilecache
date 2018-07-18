<?php

/**
 * Abstract rule test.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Tests\Unit\Cache\Rule;

use TYPO3\CMS\Core\Cache\Backend\NullBackend;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Abstract rule test.
 */
abstract class AbstractRuleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Get TSFE.
     *
     * @return TypoScriptFrontendController
     */
    public function getTsfe()
    {
        // Init the cache_pages cache, to avoid exceptions in thes TSFE building process
        $cacheManager = GeneralUtility::makeInstance(CacheManager::class);
        $cacheManager->setCacheConfigurations([
            'cache_pages' => [
                'frontend' => VariableFrontend::class,
                'backend' => NullBackend::class,
            ],
        ]);

        return new TypoScriptFrontendController([], 0, 0);
    }
}

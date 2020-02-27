<?php

/**
 * Abstract test.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Tests\Unit;

use TYPO3\CMS\Core\Cache\Backend\NullBackend;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Abstract test.
 *
 * @internal
 * @coversNothing
 */
abstract class AbstractTest extends UnitTestCase
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

        $currentVersion = VersionNumberUtility::convertVersionNumberToInteger(VersionNumberUtility::getCurrentTypo3Version());
        if($currentVersion >= 10003000) {
            $cacheManager->setCacheConfigurations([
                'pages' => [
                    'frontend' => VariableFrontend::class,
                    'backend' => NullBackend::class,
                ],
            ]);

            return new TypoScriptFrontendController(new Context(), 0, 0);

        } else {
            $cacheManager->setCacheConfigurations([
                'cache_pages' => [
                    'frontend' => VariableFrontend::class,
                    'backend' => NullBackend::class,
                ],
            ]);

            return new TypoScriptFrontendController([], 0, 0);
        }
    }
}

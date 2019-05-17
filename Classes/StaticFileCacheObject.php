<?php
/**
 * Generic object for Static File Cache incl. Logging
 */
namespace SFC\Staticfilecache;

use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Generic object for Static File Cache incl. Logging
 */
abstract class StaticFileCacheObject implements SingletonInterface
{
    use LoggerAwareTrait;

    /**
     * AbstractGenerator constructor.
     */
    public function __construct()
    {
        $this->setLogger(GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__));
    }
}

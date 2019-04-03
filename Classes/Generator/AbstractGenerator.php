<?php
/**
 * AbstractGenerator.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Generator;

use Psr\Log\LoggerAwareTrait;
use SFC\Staticfilecache\StaticFileCacheSingletonInterface;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * AbstractGenerator.
 */
abstract class AbstractGenerator implements StaticFileCacheSingletonInterface
{

    use LoggerAwareTrait;

    /**
     * AbstractGenerator constructor.
     */
    public function __construct()
    {
        $this->setLogger(GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__));
    }

    /**
     * Generate file.
     *
     * @param string $entryIdentifier
     * @param string $fileName
     * @param string $data
     */
    abstract public function generate(string $entryIdentifier, string $fileName, string $data);

    /**
     * Remove file.
     *
     * @param string $entryIdentifier
     * @param string $fileName
     */
    abstract public function remove(string $entryIdentifier, string $fileName);
}

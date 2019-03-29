<?php
/**
 * AbstractGenerator.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Generator;

use SFC\Staticfilecache\StaticFileCacheSingletonInterface;

/**
 * AbstractGenerator.
 */
abstract class AbstractGenerator implements StaticFileCacheSingletonInterface
{
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

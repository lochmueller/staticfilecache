<?php
/**
 * AbstractGenerator.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Generator;

use SFC\Staticfilecache\StaticFileCacheObject;

/**
 * AbstractGenerator.
 */
abstract class AbstractGenerator extends StaticFileCacheObject
{

    /**
     * Generate file.
     *
     * @param string $entryIdentifier
     * @param string $fileName
     * @param string $data
     * @param int $lifetime
     */
    abstract public function generate(string $entryIdentifier, string $fileName, string &$data, int $lifetime): void;

    /**
     * Remove file.
     *
     * @param string $entryIdentifier
     * @param string $fileName
     */
    abstract public function remove(string $entryIdentifier, string $fileName): void;
}

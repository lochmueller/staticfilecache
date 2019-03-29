<?php
/**
 * AbstractGenerator.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Generator;

/**
 * AbstractGenerator.
 */
abstract class AbstractGenerator
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

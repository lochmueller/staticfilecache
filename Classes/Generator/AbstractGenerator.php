<?php
/**
 * AbstractGenerator.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Generator;

use Psr\Http\Message\ResponseInterface;
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
     * @param ResponseInterface $response
     * @param int $lifetime
     */
    abstract public function generate(string $entryIdentifier, string $fileName, ResponseInterface &$response, int $lifetime): void;

    /**
     * Remove file.
     *
     * @param string $entryIdentifier
     * @param string $fileName
     */
    abstract public function remove(string $entryIdentifier, string $fileName): void;
}

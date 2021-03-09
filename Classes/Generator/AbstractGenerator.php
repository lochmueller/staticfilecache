<?php
/**
 * AbstractGenerator.
 */

declare(strict_types=1);

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
     */
    abstract public function generate(string $entryIdentifier, string $fileName, ResponseInterface $response, int $lifetime): void;

    /**
     * Remove file.
     */
    abstract public function remove(string $entryIdentifier, string $fileName): void;
}

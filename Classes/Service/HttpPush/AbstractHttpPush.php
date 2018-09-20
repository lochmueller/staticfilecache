<?php

declare(strict_types = 1);
/**
 * AbstractHttpPush.
 */
namespace SFC\Staticfilecache\Service\HttpPush;

/**
 * AbstractHttpPush.
 */
abstract class AbstractHttpPush
{
    /**
     * Check if the class can handle the file extension.
     *
     * @param string $fileExtension
     *
     * @return bool
     */
    abstract public function canHandleExtension(string $fileExtension): bool;

    /**
     * Get headers for the current file extension.
     *
     * @param string $content
     *
     * @return array
     */
    abstract public function getHeaders(string $content): array;
}

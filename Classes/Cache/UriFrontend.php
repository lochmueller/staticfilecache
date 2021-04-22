<?php

/**
 * Cache frontend for StaticFileCache.
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Cache;

use TYPO3\CMS\Core\Cache\Backend\TransientBackendInterface;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Cache frontend for StaticFileCache.
 */
class UriFrontend extends VariableFrontend
{
    /**
     * Check if the identifier is a valid URI incl. host and path.
     *
     * @param string $requestUri
     *
     * @return bool
     */
    public function isValidEntryIdentifier($requestUri)
    {
        try {
            $identifierBuilder = GeneralUtility::makeInstance(IdentifierBuilder::class);
            return $identifierBuilder->isValidEntryUri($requestUri);
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * Finds and returns a variable value from the cache.
     *
     * @param string $entryIdentifier Identifier of the cache entry to fetch
     *
     * @return mixed The value
     * @throws \InvalidArgumentException if the identifier is not valid
     */
    public function get($entryIdentifier)
    {
        $identifierBuilder = GeneralUtility::makeInstance(IdentifierBuilder::class);
        if (!$identifierBuilder->isValidEntryIdentifier($entryIdentifier)) {
            throw new \InvalidArgumentException(
                $entryIdentifier . '" is not a valid cache entry identifier.',
                1233058294
            );
        }
        $rawResult = $this->backend->get($entryIdentifier);
        if ($rawResult === false) {
            return false;
        }
        return $this->backend instanceof TransientBackendInterface ? $rawResult : unserialize($rawResult, ['allowed_classes' => false]);
    }

    /**
     * Finds and returns all cache entries which are tagged by the specified tag.
     *
     * @param string $tag The tag to search for
     *
     * @throws \InvalidArgumentException if the tag is not valid
     *
     * @return array An array with the content of all matching entries. An empty array if no entries matched
     *
     * @api
     */
    public function getByTag($tag)
    {
        if (!$this->isValidTag($tag)) {
            throw new \InvalidArgumentException('"'.$tag.'" is not a valid tag for a cache entry.', 1233058312);
        }
        $entries = [];
        $identifiers = $this->backend->findIdentifiersByTag($tag);
        foreach ($identifiers as $identifier) {
            $rawResult = $this->backend->get($identifier);
            if (false !== $rawResult) {
                $entries[$identifier] = $this->backend instanceof TransientBackendInterface ? $rawResult : unserialize($rawResult);
            }
        }

        return $entries;
    }
}

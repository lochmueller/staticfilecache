<?php

/**
 * Cache frontend for static file cache.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Cache;

use TYPO3\CMS\Core\Cache\Backend\TransientBackendInterface;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;

/**
 * Cache frontend for static file cache.
 */
class UriFrontend extends VariableFrontend
{
    /**
     * Check if the identifier is a valid URI incl. host and path.
     *
     * @param string $identifier
     *
     * @return bool
     */
    public function isValidEntryIdentifier($identifier)
    {
        if (false === \filter_var($identifier, FILTER_VALIDATE_URL)) {
            return false;
        }
        $urlParts = \parse_url($identifier);
        $required = ['host', 'path', 'scheme'];
        foreach ($required as $item) {
            if (!isset($urlParts[$item]) || \mb_strlen($urlParts[$item]) <= 0) {
                return false;
            }
        }

        return true;
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
     *
     * @todo migrate in TYPO3 v10 to PSR-6 pools
     */
    public function getByTag($tag)
    {
        if (!$this->isValidTag($tag)) {
            throw new \InvalidArgumentException('"' . $tag . '" is not a valid tag for a cache entry.', 1233058312);
        }
        $entries = [];
        $identifiers = $this->backend->findIdentifiersByTag($tag);
        foreach ($identifiers as $identifier) {
            $rawResult = $this->backend->get($identifier);
            if (false !== $rawResult) {
                $entries[$identifier] = $this->backend instanceof TransientBackendInterface ? $rawResult : \unserialize($rawResult);
            }
        }

        return $entries;
    }
}

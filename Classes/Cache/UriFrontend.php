<?php
/**
 * Cache frontend for static file cache.
 */
declare(strict_types=1);

namespace SFC\Staticfilecache\Cache;

use TYPO3\CMS\Core\Cache\Backend\TaggableBackendInterface;
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
     * Saves the value of a PHP variable in the cache.
     *
     * @param string $entryIdentifier An identifier used for this cache entry
     * @param string $string          The variable to cache
     * @param array  $tags            Tags to associate with this cache entry
     * @param int    $lifetime        Lifetime of this cache entry in seconds. NULL=default; 0=unlimited
     *
     * @throws \InvalidArgumentException if the identifier or tag is not valid
     *
     * @api
     */
    public function set($entryIdentifier, $string, array $tags = [], $lifetime = null)
    {
        if (!$this->isValidEntryIdentifier($entryIdentifier)) {
            throw new \InvalidArgumentException(
                '"' . $entryIdentifier . '" is not a valid cache entry identifier.',
                1233057566
            );
        }
        foreach ($tags as $tag) {
            if (!$this->isValidTag($tag)) {
                throw new \InvalidArgumentException('"' . $tag . '" is not a valid tag for a cache entry.', 1233057512);
            }
        }
        $this->backend->set($entryIdentifier, $string, $tags, $lifetime);
    }

    /**
     * Finds and returns all cache entries which are tagged by the specified tag.
     *
     * @param string $tag The tag to search for
     *
     * @return array An array with the content of all matching entries. An empty array if no entries matched
     */
    public function getByTag($tag)
    {
        if (!$this->isValidTag($tag)) {
            throw new \InvalidArgumentException('"' . $tag . '" is not a valid tag for a cache entry.', 1233057772);
        }
        if (!($this->backend instanceof TaggableBackendInterface)) {
            return [];
        }
        $identifiers = $this->backend->findIdentifiersByTag($tag);
        $return = [];
        foreach ($identifiers as $identifier) {
            $return[$identifier] = $this->get($identifier);
        }

        return $return;
    }
}

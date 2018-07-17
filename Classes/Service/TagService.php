<?php

/**
 * TagService.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Service;

use SFC\Staticfilecache\Configuration;

/**
 * TagService.
 */
class TagService extends AbstractService
{
    /**
     * Get the tags and respect the configuration.
     *
     * @return array
     */
    public function getTags(): array
    {
        $config = $this->getConfiguration();
        if (!$config['cacheTagsEnable']) {
            return [];
        }

        // get tags @todo

        return [];
    }

    /**
     * Get header name.
     *
     * @return string
     */
    public function getHeaderName(): string
    {
        return $this->getConfiguration()['cacheTagsHeaderName'];
    }

    /**
     * Get the configuration.
     *
     * @return array
     */
    protected function getConfiguration(): array
    {
        $config = Configuration::getConfiguration();

        return [
            'cacheTagsEnable' => $config['cacheTagsEnable'] ?? false,
            'cacheTagsHeaderName' => isset($config['cacheTagsHeaderName']) && \mb_strlen((string)$config['cacheTagsHeaderName']) ? (string)$config['cacheTagsHeaderName'] : 'X-Cache-Tags',
        ];
    }
}

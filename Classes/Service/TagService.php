<?php

/**
 * TagService.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Service;

use SFC\Staticfilecache\Configuration;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

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
        /** @var TypoScriptFrontendController $tsfe */
        $tsfe = $GLOBALS['TSFE'];
        if (!($tsfe instanceof TypoScriptFrontendController)) {
            return [];
        }

        return \array_unique((array)ObjectAccess::getProperty($tsfe, 'pageCacheTags', true));
    }

    /**
     * Check if it is enable.
     *
     * @return bool
     */
    public function isEnable(): bool
    {
        $config = $this->getConfiguration();

        return (bool)$config['cacheTagsEnable'];
    }

    /**
     * Send the cache headers.
     */
    public function send()
    {
        if (!$this->isEnable()) {
            return;
        }

        $tags = $this->getTags();
        if (!empty($tags)) {
            \header($this->getHeaderName() . ': ' . \implode(',', $tags));
        }
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

<?php
/**
 * TypoScriptFrontendService.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Service;

use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * TypoScriptFrontendService.
 */
class TypoScriptFrontendService
{
    /**
     * Get the tags and respect the configuration.
     *
     * @return array
     */
    public function getTags(): array
    {
        $tsfe = $this->getTsfe();
        if (!($tsfe instanceof TypoScriptFrontendController)) {
            return [];
        }

        return \array_unique((array)ObjectAccess::getProperty($tsfe, 'pageCacheTags', true));
    }

    /**
     * Get additional headers.
     *
     * @return array
     */
    public function getAdditionalHeaders(): array
    {
        $headers = [];
        $tsfe = $this->getTsfe();
        if (!($tsfe instanceof TypoScriptFrontendController)) {
            return $headers;
        }
        // Set headers, if any
        if (\is_array($tsfe->config['config']['additionalHeaders.'])) {
            \ksort($tsfe->config['config']['additionalHeaders.']);
            foreach ($tsfe->config['config']['additionalHeaders.'] as $options) {
                $complete = \trim($options['header']);
                $parts = \explode(':', $complete, 2);
                $headers[\trim($parts[0])] = \trim($parts[1]);
            }
        }

        return $headers;
    }

    /**
     * Get the TSFE.
     *
     * @return TypoScriptFrontendController
     */
    protected function getTsfe()
    {
        return $GLOBALS['TSFE'];
    }
}

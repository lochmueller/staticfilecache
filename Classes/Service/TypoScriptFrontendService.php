<?php
/**
 * TypoScriptFrontendService.
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Service;

use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * TypoScriptFrontendService.
 */
class TypoScriptFrontendService extends AbstractService
{
    /**
     * Get the tags and respect the configuration.
     */
    public function getTags(): array
    {
        $tsfe = $this->getTsfe();
        if (!($tsfe instanceof TypoScriptFrontendController)) {
            return [];
        }

        return array_unique((array) $tsfe->getPageCacheTags());
    }

    /**
     * Get the TSFE.
     *
     * @return TypoScriptFrontendController
     */
    protected function getTsfe(): ?TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }
}

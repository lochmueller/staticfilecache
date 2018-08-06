<?php

/**
 * Eofe.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Hook\Cache;

use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Eofe.
 */
class Eofe extends AbstractCacheHook
{
    /**
     * Insert cache entry.
     *
     * @param array                        $params
     * @param TypoScriptFrontendController $tsfe
     *
     * @throws \TYPO3\CMS\Core\Cache\Exception\InvalidDataException
     */
    public function insert($params, TypoScriptFrontendController $tsfe)
    {
        $this->getStaticFileCache()->insertPageInCache($tsfe);
    }
}

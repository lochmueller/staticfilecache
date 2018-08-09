<?php

/**
 * Check if the current domain is static cacheable in Domain property context.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Cache\Rule;

use SFC\Staticfilecache\Domain\Repository\DomainRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Check if the current domain is static cacheable in Domain property context.
 */
class DomainCacheable extends AbstractRule
{
    /**
     * Check if the current domain is static cacheable in Domain property context.
     *
     * @param TypoScriptFrontendController $frontendController
     * @param string                       $uri
     * @param array                        $explanation
     * @param bool                         $skipProcessing
     */
    public function checkRule(TypoScriptFrontendController $frontendController, string $uri, array &$explanation, bool &$skipProcessing)
    {
        $domainString = \parse_url($uri, PHP_URL_HOST);
        $domainRepository = GeneralUtility::makeInstance(DomainRepository::class);
        $domain = $domainRepository->findOneByDomainName($domainString);
        if (!empty($domain)) {
            $cachableDomain = (bool)$domain['tx_staticfilecache_cache'];
            if (!$cachableDomain) {
                $explanation[__CLASS__] = 'static cache disabled on domain: ' . $domainString;
            }
        }
    }
}

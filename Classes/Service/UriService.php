<?php

/**
 * UriService.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Service;

use Mso\IdnaConvert\IdnaConvert;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * UriService.
 */
class UriService extends AbstractService
{
    /**
     * get the URI for the current cache ident.
     *
     * @return string
     */
    public function getUri(): string
    {
        $configuration = GeneralUtility::makeInstance(ConfigurationService::class);

        // Find host-name / IP, always in lowercase:
        $isHttp = (0 === \mb_strpos(GeneralUtility::getIndpEnv('TYPO3_REQUEST_HOST'), 'http://'));
        $uri = GeneralUtility::getIndpEnv('REQUEST_URI');
        $uri = ($isHttp ? 'http://' : 'https://') . \mb_strtolower(GeneralUtility::getIndpEnv('HTTP_HOST')) . '/' . \ltrim($uri, '/');

        try {
            if (class_exists(IdnaConvert::class)) {
                // Note: https://github.com/algo26-matthias/idna-convert
                // Note: https://forge.typo3.org/issues/87779
                $idnaConverter = GeneralUtility::makeInstance(IdnaConvert::class);
                return $idnaConverter->encode($uri);
            }
        } catch (\InvalidArgumentException $exception) {
            // The URI is already in puny code (no logging needed)
        }
        return $uri;
    }
}

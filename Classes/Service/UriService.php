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
        if ($configuration->isBool('recreateURI')) {
            $uri = $this->recreateUriPath($uri);
        }

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

    /**
     * Recreates the URI of the current request.
     *
     * Especially in simulateStaticDocument context, the different URIs lead to the same result
     * and static file caching would store the wrong URI that was used in the first request to
     * the website (e.g. "TheGoodURI.13.0.html" is as well accepted as "TheFakeURI.13.0.html")
     *
     * @param string $uri
     *
     * @return string The recreated URI of the current request
     */
    protected function recreateUriPath($uri): string
    {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        /** @var UriBuilder $uriBuilder */
        $uriBuilder = $objectManager->get(UriBuilder::class);
        if (null === ObjectAccess::getProperty($uriBuilder, 'contentObject', true)) {
            // there are situations without a valid contentObject in the URI builder
            // prevent this situation by return the original request URI
            return $uri;
        }
        $url = $uriBuilder->reset()
            ->setAddQueryString(true)
            ->setCreateAbsoluteUri(true)
            ->build();

        $parts = (array)\parse_url($url);
        $unset = ['scheme', 'user', 'pass', 'host', 'port'];
        foreach ($unset as $u) {
            unset($parts[$u]);
        }

        return HttpUtility::buildUrl($parts);
    }
}

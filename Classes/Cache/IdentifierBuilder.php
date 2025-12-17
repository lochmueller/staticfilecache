<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Cache;

use Psr\EventDispatcher\EventDispatcherInterface;
use SFC\Staticfilecache\Event\BuildIdentifierEvent;
use SFC\Staticfilecache\Exception;
use SFC\Staticfilecache\Service\CacheService;
use SFC\Staticfilecache\Service\ConfigurationService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class IdentifierBuilder
{
    public function __construct(protected ?EventDispatcherInterface $eventDispatcher = null)
    {
        if ($this->eventDispatcher === null) {
            $this->eventDispatcher = GeneralUtility::getContainer()->get(EventDispatcherInterface::class);
        }
    }

    /**
     * Get the cache name for the given URI.
     *
     * @throws \Exception
     */
    public function getFilepath(string $requestUri): string
    {
        if (!$this->isValidEntryIdentifier($requestUri)) {
            throw new \Exception('Invalid RequestUri as cache identifier: ' . $requestUri, 2346782);
        }
        $urlParts = parse_url($requestUri);
        $pageIdentifier = [
            'scheme' => strtolower($urlParts['scheme'] ?? 'https'),
            'host' => strtolower($urlParts['host'] ?? 'invalid'),
            'port' => $urlParts['port'] ?? ('https' === $urlParts['scheme'] ? 443 : 80),
        ];
        $parts = [
            'pageIdent' => implode('_', $pageIdentifier),
            'path' => trim($urlParts['path'] ?? '', '/'),
            'index' => 'index',
        ];

        if (GeneralUtility::makeInstance(ConfigurationService::class)->isBool('rawurldecodeCacheFileName')) {
            $parts['path'] = rawurldecode($parts['path']);
        }

        /** @var BuildIdentifierEvent $buildIdentifier */
        $buildIdentifier = $this->eventDispatcher->dispatch(new BuildIdentifierEvent($requestUri, $parts));

        $absoluteBasePath = GeneralUtility::makeInstance(CacheService::class)->getAbsoluteBaseDirectory();
        $resultPath = GeneralUtility::resolveBackPath($absoluteBasePath . implode('/', $buildIdentifier->getParts()));

        if (!str_starts_with($resultPath, $absoluteBasePath)) {
            throw new Exception('The generated filename "' . $resultPath . '" should start with the cache directory "' . $absoluteBasePath . '"', 123781);
        }

        return $resultPath;
    }

    /**
     * Check if the $requestUri is a valid base for cache identifier.
     */
    public function isValidEntryIdentifier(string $requestUri): bool
    {
        if (false === GeneralUtility::isValidUrl($requestUri)) {
            return false;
        }
        $urlParts = parse_url($requestUri);
        $required = ['host', 'path', 'scheme'];
        foreach ($required as $item) {
            if (!isset($urlParts[$item]) || mb_strlen($urlParts[$item]) <= 0) {
                return false;
            }
        }

        return true;
    }
}

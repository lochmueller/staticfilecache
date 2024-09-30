<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Service\HttpPush;

use SFC\Staticfilecache\Event\HttpPushHeaderEvent;
use SFC\Staticfilecache\Service\ObjectFactoryService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FontHttpPush extends AbstractHttpPush
{
    /**
     * Last checked extension.
     */
    protected ?string $lastExtension;

    /**
     * Fonts extensions.
     */
    private $fontsExtensions = ['woff', 'woff2'];


    /**
     * Check if the class can handle the file extension.
     */
    public function canHandleExtension(string $fileExtension): bool
    {
        $handle = \in_array($fileExtension, $this->fontsExtensions, true);
        if ($handle) {
            $this->lastExtension = $fileExtension;
        }

        return $handle;
    }

    /**
     * Get headers for the current file extension.
     */
    public function getHeaders(string $content): array
    {
        if (null === $this->lastExtension) {
            return [];
        }

        if (!preg_match_all('/(?<=")(?<src>[^"]+?\.' . $this->lastExtension . ')(?=")/', $content, $fontFiles)) {
            return [];
        }

        $paths = $this->streamlineFilePaths((array) $fontFiles['src']);

        return $this->mapPathsWithType($paths, 'font');
    }
}

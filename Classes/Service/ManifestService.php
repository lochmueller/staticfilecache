<?php

/**
 * ManifestService.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Service;

use SFC\Staticfilecache\Cache\IdentifierBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * ManifestService.
 *
 * For handling the Offline functions
 */
class ManifestService extends AbstractService
{

    /**
     * Write the manifest file
     */
    public function writeManifestFile()
    {
    }

    /**
     * Frontend call of the appcache files
     */
    public function callEid()
    {
        header('Content-Type: text/cache-manifest');
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: ' . date(DATE_RFC1123));

        try {
            $identifierBuilder = GeneralUtility::makeInstance(IdentifierBuilder::class);
            $fileName = $identifierBuilder->getCacheFilename(GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL'));
            var_dump($fileName);
        } catch (\Exception $ex) {
        }
    }
}

<?php
/**
 * PublishService
 *
 * @author  Tim Lochmüller
 */

namespace SFC\Staticfilecache\Service;

use SFC\Staticfilecache\Cache\StaticFileBackend;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * PublishService
 */
class PublishService implements SingletonInterface
{

    /**
     * Publish
     */
    public function publish()
    {
        $cacheDir = StaticFileBackend::CACHE_DIRECTORY;

        // @todo handle scheme
        // @todo handle host

        // @todo call signal and preperation


    }

}

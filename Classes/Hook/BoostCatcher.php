<?php
/**
 * Catch the cache calls for the boost mode
 *
 * @author  Tim Lochmüller
 */

namespace SFC\Staticfilecache\Hook;

use SFC\Staticfilecache\Configuration;
use SFC\Staticfilecache\QueueManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * Catch the cache calls for the boost mode
 */
class BoostCatcher
{

    /**
     * Clear cache post proc
     *
     * @param $params
     * @param $object
     */
    public function clearCachePostProc($params, $object)
    {

        $configuration = GeneralUtility::makeInstance(Configuration::class);
        if ((bool)$configuration->get('boostMode')) {
            $pages = '';
            if (isset($params['uid_page'])) {
                $pages .= ',' . $params['uid_page'];
            }
            if (isset($params['cacheCmd'])) {
                $pages .= ',' . $params['cacheCmd'];
            }
            foreach (GeneralUtility::intExplode(',', $pages, true) as $pid) {
                $this->getQueueManager()
                    ->clearCacheForPage($pid);
            }
        }
    }

    /**
     * @return QueueManager
     */
    protected function getQueueManager()
    {
        return GeneralUtility::makeInstance(QueueManager::class);
    }
}

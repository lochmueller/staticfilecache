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
        $configuration = new Configuration();
        if ((bool)$configuration->get('boostMode')) {
            if (isset($params['uid_page'])) {
                $this->getQueueManager()
                    ->clearCacheForPage($params['uid_page']);
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

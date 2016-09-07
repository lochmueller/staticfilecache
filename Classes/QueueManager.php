<?php
/**
 * Queue manager
 *
 * @author  Tim Lochmüller
 */

namespace SFC\Staticfilecache;

use TYPO3\CMS\Core\SingletonInterface;

/**
 * Queue manager
 */
class QueueManager implements SingletonInterface
{

    /**
     * Queue table
     */
    const QUEUE_TABLE = 'tx_staticfilecache_queue';

    /**
     * Run the queue
     */
    public function run()
    {

        // @todo run throw queue
    }

    /**
     * @param $pageUid
     */
    public function clearCacheForPage($pageUid)
    {

        // @todo implement
        //  'sfc_pageId_' . $pObj->page['uid']
        #var_dump($pageUid);
    }
}

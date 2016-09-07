<?php
/**
 * Queue manager
 *
 * @author  Tim Lochmüller
 */

namespace SFC\Staticfilecache;

use SFC\Staticfilecache\Cache\UriFrontend;
use SFC\Staticfilecache\Utility\CacheUtility;
use TYPO3\CMS\Core\Database\DatabaseConnection;
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
     * @var UriFrontend
     */
    protected $cache;

    /**
     * QueueManager constructor.
     */
    public function __construct()
    {
        $this->cache = CacheUtility::getCache();
        ;
    }

    /**
     * Run the queue
     */
    public function run()
    {
        // @todo run throw queue
    }

    /**
     * Add the given page information to the cache
     *
     * @param int $pageUid
     */
    public function clearCacheForPage($pageUid)
    {
        $urls = array_keys($this->cache->getByTag('sfc_pageId_' . $pageUid));
        $fields = [
            'cache_url',
            'invalid_date',
            'call_result'
        ];
        $rows = [];
        foreach ($urls as $url) {
            $rows[] = [
                $url,
                time(),
                ''
            ];
        }
        $this->getDatabaseConnection()
            ->exec_INSERTmultipleRows(self::QUEUE_TABLE, $fields, $rows);
    }

    /**
     * Get the database connection
     *
     * @return DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }
}

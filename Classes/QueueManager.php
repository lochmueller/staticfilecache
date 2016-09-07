<?php
/**
 * Queue manager
 *
 * @author  Tim Lochmüller
 */

namespace SFC\Staticfilecache;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;
use SFC\Staticfilecache\Cache\UriFrontend;
use SFC\Staticfilecache\Utility\CacheUtility;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
        $dbConnection = $this->getDatabaseConnection();
        $runEntries = $dbConnection->exec_SELECTgetRows('*', self::QUEUE_TABLE, 'call_date=0');

        if (empty($runEntries)) {
            return;
        }

        $client = $this->getCallableClient();

        foreach ($runEntries as $runEntry) {
            $response = $client->get($runEntry['cache_url']);
            $statusCode = $response->getStatusCode();
            $data = [
                'call_date'   => time(),
                'call_result' => $statusCode,
            ];

            if ($statusCode !== 200) {
                // Call the flush, if the page is not accessable
                $this->cache->flushByTag('sfc_pageId_' . $runEntry['page_uid']);
            }
            $dbConnection->exec_UPDATEquery(self::QUEUE_TABLE, 'uid=' . $runEntry['uid'], $data);
        }
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
            'page_uid',
            'invalid_date',
            'call_result'
        ];
        $rows = [];
        foreach ($urls as $url) {
            $rows[] = [
                $url,
                $pageUid,
                time(),
                ''
            ];
        }
        $this->getDatabaseConnection()
            ->exec_INSERTmultipleRows(self::QUEUE_TABLE, $fields, $rows);
    }

    /**
     * Get a cllable client
     *
     * @return Client
     */
    protected function getCallableClient()
    {
        $jar = GeneralUtility::makeInstance(CookieJar::class);
        $cookie = GeneralUtility::makeInstance(SetCookie::class);
        $cookie->setName('staticfilecache');
        $cookie->setValue('1');
        $jar->setCookie($cookie);
        $options = [
            'cookies' => $jar,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:47.0) Gecko/20100101 Firefox/47.0'
            ]
        ];
        return GeneralUtility::makeInstance(Client::class, $options);
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

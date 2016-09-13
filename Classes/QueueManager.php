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
use SFC\Staticfilecache\Utility\CacheUtility;
use SFC\Staticfilecache\Utility\ComposerUtility;
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
     * Run the queue
     */
    public function run()
    {
        $dbConnection = $this->getDatabaseConnection();
        $runEntries = $dbConnection->exec_SELECTgetRows('*', self::QUEUE_TABLE, 'call_date=0');

        if (empty($runEntries)) {
            return;
        }

        $cache = CacheUtility::getCache();

        foreach ($runEntries as $runEntry) {
            $client = $this->getCallableClient(parse_url($runEntry['cache_url'], PHP_URL_HOST));
            $response = $client->get($runEntry['cache_url']);
            $statusCode = $response->getStatusCode();
            $data = [
                'call_date'   => time(),
                'call_result' => $statusCode,
            ];

            if ($statusCode !== 200) {
                // Call the flush, if the page is not accessable
                $cache->flushByTag('sfc_pageId_' . $runEntry['page_uid']);
            }
            $dbConnection->exec_UPDATEquery(self::QUEUE_TABLE, 'uid=' . $runEntry['uid'], $data);
        }
    }

    /**
     * @param string $identifier
     */
    public function addIdentifier($identifier)
    {
        $db = $this->getDatabaseConnection();
        $row = $db->exec_SELECTgetSingleRow('*', self::QUEUE_TABLE, 'cache_url="' . $identifier . '" AND call_date=0');
        if (is_array($row)) {
            return;
        }
        $data = [
            'cache_url'    => $identifier,
            'page_uid'     => 0,
            'invalid_date' => time(),
            'call_result'  => ''
        ];
        $db->exec_INSERTquery(self::QUEUE_TABLE, $data);
    }

    /**
     * Get a cllable client
     *
     * @param string $domain
     *
     * @return Client
     */
    protected function getCallableClient($domain)
    {
        ComposerUtility::check();
        $jar = GeneralUtility::makeInstance(CookieJar::class);
        $cookie = GeneralUtility::makeInstance(SetCookie::class);
        $cookie->setName('staticfilecache');
        $cookie->setValue('1');
        $cookie->setPath('/');
        $cookie->setExpires(time() + 30);
        $cookie->setDomain($domain);
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

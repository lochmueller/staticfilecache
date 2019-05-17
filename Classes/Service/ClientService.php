<?php

/**
 * ClientService.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

/**
 * ClientService.
 */
class ClientService extends AbstractService
{
    /**
     * Run a single request with guzzle and return status code.
     *
     * @param string $url
     *
     * @return int
     */
    public function runSingleRequest(string $url): int
    {
        try {
            $host = \parse_url($url, PHP_URL_HOST);
            if (false === $host) {
                throw new \Exception('No host in cache_url', 1263782);
            }
            $client = $this->getCallableClient($host);
            $response = $client->get($url);

            return (int)$response->getStatusCode();
        } catch (\Exception $exception) {
            $this->logger->error('Problems in single request running: ' . $exception->getMessage() . ' / ' . $exception->getFile() . ':' . $exception->getLine());
        }

        return 900;
    }

    /**
     * Get a cllable client.
     *
     * @param string $domain
     *
     * @throws \Exception
     *
     * @return Client
     */
    protected function getCallableClient(string $domain): Client
    {
        $jar = GeneralUtility::makeInstance(CookieJar::class);
        $cookie = GeneralUtility::makeInstance(SetCookie::class);
        $cookie->setName('staticfilecache');
        $cookie->setValue('1');
        $cookie->setPath('/');
        $cookie->setExpires((new DateTimeService())->getCurrentTime() + 3600);
        $cookie->setDomain($domain);
        $jar->setCookie($cookie);
        $options = [
            'cookies' => $jar,
            'allow_redirects' => [
                'max' => false,
            ],
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.12; rv:54.0) Gecko/20100101 Firefox/54.0',
            ],
        ];

        // Core options
        $httpOptions = (array)$GLOBALS['TYPO3_CONF_VARS']['HTTP'];
        $httpOptions['verify'] = filter_var($httpOptions['verify'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $httpOptions['verify'];

        // extended
        $params = [
            'sfc' => $options,
            'core' => $httpOptions,
        ];
        $signalSlotDispatcher = GeneralUtility::makeInstance(Dispatcher::class);
        $params = $signalSlotDispatcher->dispatch(__CLASS__, 'getCallableClient', $params);
        $base = $params['core'];
        ArrayUtility::mergeRecursiveWithOverrule($base, $params['sfc']);

        return GeneralUtility::makeInstance(Client::class, $base);
    }
}

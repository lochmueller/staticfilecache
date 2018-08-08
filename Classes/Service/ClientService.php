<?php

/**
 * ClientService.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * ClientService.
 */
class ClientService
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
        } catch (\Exception $ex) {
            // @todo logging
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
        if (!\class_exists(Client::class) || !\class_exists(CookieJar::class)) {
            throw new \Exception('You need guzzle to handle the Queue Management', 1236728342);
        }
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

        return GeneralUtility::makeInstance(Client::class, $options);
    }
}

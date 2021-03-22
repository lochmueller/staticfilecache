<?php

/**
 * ClientService.
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;
use GuzzleHttp\HandlerStack;
use Psr\EventDispatcher\EventDispatcherInterface;
use SFC\Staticfilecache\Event\BuildClientEvent;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * ClientService.
 */
class ClientService extends AbstractService
{
    protected EventDispatcherInterface $eventDispatcher;

    /**
     * PrepareMiddleware constructor.
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Run a single request with guzzle and return status code.
     */
    public function runSingleRequest(string $url): int
    {
        try {
            $host = parse_url($url, PHP_URL_HOST);
            if (false === $host) {
                throw new \Exception('No host in cache_url', 1263782);
            }
            $client = $this->getCallableClient($host);
            $response = $client->get($url);

            return (int) $response->getStatusCode();
        } catch (\Exception $exception) {
            $this->logger->error('Problems in single request running: '.$exception->getMessage().' / '.$exception->getFile().':'.$exception->getLine());
        }

        return 900;
    }

    /**
     * Get a cllable client.
     *
     * @throws \Exception
     */
    protected function getCallableClient(string $domain): Client
    {
        $jar = GeneralUtility::makeInstance(CookieJar::class);
        $cookie = GeneralUtility::makeInstance(SetCookie::class);
        $cookie->setName(CookieService::FE_COOKIE_NAME);
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
        $httpOptions = (array) $GLOBALS['TYPO3_CONF_VARS']['HTTP'];
        $httpOptions['verify'] = filter_var($httpOptions['verify'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $httpOptions['verify'];
        if (isset($httpOptions['handler']) && \is_array($httpOptions['handler'])) {
            $stack = HandlerStack::create();
            foreach ($httpOptions['handler'] ?? [] as $handler) {
                $stack->push($handler);
            }
            $httpOptions['handler'] = $stack;
        }

        $event = new BuildClientEvent($options, $httpOptions);
        $this->eventDispatcher->dispatch($event);

        $base = $event->getHttpOptions();
        ArrayUtility::mergeRecursiveWithOverrule($base, $event->getOptions());

        return GeneralUtility::makeInstance(Client::class, $base);
    }
}

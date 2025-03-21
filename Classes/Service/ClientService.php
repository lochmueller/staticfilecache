<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use SFC\Staticfilecache\Event\BuildClientEvent;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ClientService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected const DEFAULT_USER_AGENT = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.12; rv:54.0) Gecko/20100101 Firefox/54.0';

    public function __construct(protected EventDispatcherInterface $eventDispatcher) {}

    /**
     * Run multiple requests in parallel and return status codes.
     *
     * @param array $urls List of URLs to process
     * @param int $concurrency Number of concurrent requests
     * @return array Status codes indexed same as input URLs
     */
    public function runMultipleRequests(array $urls, int $concurrency = 5): array
    {
        if (empty($urls)) {
            return [];
        }

        $domains = [];
        foreach ($urls as $url) {
            $host = parse_url($url, PHP_URL_HOST);
            if ($host) {
                $domains[$url] = $host;
            }
        }

        if (empty($domains)) {
            return array_fill(0, count($urls), 900);
        }

        try {
            $cookies = $this->prepareSessionCookies($domains);

            $clients = array_map(fn($domain) => $this->getCallableClient($domain), $domains);

            $requests = static function ($urls) use ($clients, $cookies) {
                foreach ($urls as $url) {
                    if (isset($clients[$url])) {
                        yield new Request('GET', $url, [
                            'cookies' => $cookies[parse_url((string) ($clients[$url]->getConfig('base_uri') ?? ''), PHP_URL_HOST)] ?? null,
                            'User-Agent' => GeneralUtility::makeInstance(ConfigurationService::class)
                                ->get('overrideClientUserAgent') ?? self::DEFAULT_USER_AGENT,
                        ]);
                    } else {
                        yield new Request('GET', $url, [
                            'User-Agent' => GeneralUtility::makeInstance(ConfigurationService::class)
                                ->get('overrideClientUserAgent') ?? self::DEFAULT_USER_AGENT,
                        ]);
                    }
                }
            };

            $statusCodes = array_fill(0, count($urls), 900);

            $poolClient = $this->getParallelClient();

            $pool = new Pool($poolClient, $requests($urls), [
                'concurrency' => $concurrency,
                'fulfilled' => static function (Response $response, $index) use (&$statusCodes): void {
                    $statusCodes[$index] = (int) $response->getStatusCode();
                },
                'rejected' => function ($reason, $index) use (&$statusCodes): void {
                    $this->logger->warning('Request failed: ' . ($reason instanceof \Exception ? $reason->getMessage() : 'Unknown error'));
                    $statusCodes[$index] = 900;
                },
            ]);

            $pool->promise()->wait();

            return $statusCodes;

        } catch (\Throwable $exception) {
            $this->logger->error('Problems in batch request processing: ' . $exception->getMessage() . ' / ' . $exception->getFile() . ':' . $exception->getLine());
            return array_fill(0, count($urls), 900);
        }
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

            return $response->getStatusCode();
        } catch (\Throwable $exception) {
            $this->logger->error('Problems in single request running: ' . $exception->getMessage() . ' / ' . $exception->getFile() . ':' . $exception->getLine());
        }

        return 900;
    }

    /**
     * Get a callable client.
     *
     * @throws \Exception
     */
    protected function getCallableClient(string $domain): Client
    {
        $jar = GeneralUtility::makeInstance(CookieJar::class);
        /** @var SetCookie $cookie */
        $cookie = GeneralUtility::makeInstance(SetCookie::class);
        $cookie->setName(CookieService::FE_COOKIE_NAME);
        $cookie->setValue('1');
        $cookie->setPath('/');
        $cookie->setExpires((new DateTimeService())->getCurrentTime() + 3600);
        $cookie->setDomain($domain);
        $cookie->setHttpOnly(true);
        $jar->setCookie($cookie);
        $options = [
            'cookies' => $jar,
            'allow_redirects' => [
                'max' => false,
            ],
            'headers' => [
                'User-Agent' => GeneralUtility::makeInstance(ConfigurationService::class)->get('overrideClientUserAgent') ?? self::DEFAULT_USER_AGENT,
            ],
        ];

        // Core options
        $httpOptions = (array) $GLOBALS['TYPO3_CONF_VARS']['HTTP'];
        $httpOptions['verify'] = filter_var($httpOptions['verify'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $httpOptions['verify'];
        if (isset($httpOptions['handler']) && \is_array($httpOptions['handler'])) {
            $stack = HandlerStack::create();
            foreach ($httpOptions['handler'] as $handler) {
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

    /**
     * Get client optimized for parallel requests
     */
    protected function getParallelClient(): Client
    {
        $options = [
            'timeout' => 30,
            'connect_timeout' => 10,
            'allow_redirects' => [
                'max' => false,
            ],
        ];

        $httpOptions = (array) $GLOBALS['TYPO3_CONF_VARS']['HTTP'];
        $httpOptions['verify'] = filter_var($httpOptions['verify'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $httpOptions['verify'];
        if (isset($httpOptions['handler']) && \is_array($httpOptions['handler'])) {
            $stack = HandlerStack::create();
            foreach ($httpOptions['handler'] as $handler) {
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

    /**
     * Prepare session cookies for multiple domains
     *
     * @param array $domains Domain names indexed by URL
     * @return array Cookie jars indexed by domain
     */
    protected function prepareSessionCookies(array $domains): array
    {
        $cookies = [];
        $uniqueDomains = array_unique(array_values($domains));
        $currentTime = (new DateTimeService())->getCurrentTime();

        foreach ($uniqueDomains as $domain) {
            $jar = GeneralUtility::makeInstance(CookieJar::class);

            /** @var SetCookie $cookie */
            $cookie = GeneralUtility::makeInstance(SetCookie::class);
            $cookie->setName(CookieService::FE_COOKIE_NAME);
            $cookie->setValue('1');
            $cookie->setPath('/');
            $cookie->setExpires($currentTime + 3600);
            $cookie->setDomain($domain);
            $cookie->setHttpOnly(true);

            $jar->setCookie($cookie);
            $cookies[$domain] = $jar;
        }

        return $cookies;
    }
}

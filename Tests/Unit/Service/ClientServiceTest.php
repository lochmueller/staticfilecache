<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Service;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Promise\Create;
use GuzzleHttp\Psr7\Response;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use SFC\Staticfilecache\Event\BuildClientEvent;
use SFC\Staticfilecache\Service\ClientService;
use SFC\Staticfilecache\Service\CookieService;
use SFC\Staticfilecache\Tests\Unit\AbstractTest;

/**
 * ClientServiceTest.
 *
 * @internal
 * @coversNothing
 */
final class ClientServiceTest extends AbstractTest
{
    /**
     * Requests that reached the (fake) handler.
     *
     * @var RequestInterface[]
     */
    protected array $requests = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->setExtensionConfiguration();
    }

    public function testRunMultipleRequestsWithoutUrls(): void
    {
        self::assertSame([], $this->getClientService()->runMultipleRequests([]));
    }

    public function testRunMultipleRequestsWithoutValidHosts(): void
    {
        self::assertSame([900, 900], $this->getClientService()->runMultipleRequests(['/relative', 'no-url']));
    }

    public function testRunMultipleRequests(): void
    {
        $this->registerFakeHandler(static function (RequestInterface $request) {
            return match ($request->getUri()->getPath()) {
                '/ok' => Create::promiseFor(new Response(200)),
                '/missing' => Create::promiseFor(new Response(404)),
                default => Create::rejectionFor(new ConnectException('Connection refused', $request)),
            };
        });

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::exactly(2))->method('warning');
        $logger->expects(self::never())->method('error');

        $clientService = $this->getClientService();
        $clientService->setLogger($logger);

        $result = $clientService->runMultipleRequests([
            'https://example.com/ok',
            'https://example.com/missing',
            'https://example.org/broken',
        ]);

        // HTTP errors (4xx/5xx) are handled as rejected requests
        self::assertSame([200, 900, 900], $result);
        self::assertCount(3, $this->requests);
        self::assertStringContainsString(CookieService::FE_COOKIE_NAME . '=1', $this->requests[0]->getHeaderLine('Cookie'));
        self::assertStringContainsString('Mozilla', $this->requests[0]->getHeaderLine('User-Agent'));
    }

    public function testRunMultipleRequestsReturnErrorCodesOnException(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['HTTP'] = ['verify' => true, 'handler' => ['no callable']];

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error');

        $clientService = $this->getClientService();
        $clientService->setLogger($logger);

        self::assertSame([900, 900], $clientService->runMultipleRequests(['https://example.com/a', 'https://example.com/b']));
    }

    public function testRunSingleRequest(): void
    {
        $this->registerFakeHandler(static fn() => Create::promiseFor(new Response(301)));

        $result = $this->getClientService()->runSingleRequest('https://example.com/page');

        self::assertSame(301, $result);
        self::assertCount(1, $this->requests);
        self::assertSame('https://example.com/page', (string) $this->requests[0]->getUri());
        self::assertStringContainsString(CookieService::FE_COOKIE_NAME . '=1', $this->requests[0]->getHeaderLine('Cookie'));
        self::assertStringContainsString('Mozilla', $this->requests[0]->getHeaderLine('User-Agent'));
    }

    public function testRunSingleRequestUseUserAgentOverride(): void
    {
        $this->setExtensionConfiguration(['overrideClientUserAgent' => 'SFC-Bot']);
        $this->registerFakeHandler(static fn() => Create::promiseFor(new Response(200)));

        $this->getClientService()->runSingleRequest('https://example.com/page');

        self::assertSame('SFC-Bot', $this->requests[0]->getHeaderLine('User-Agent'));
    }

    public function testRunSingleRequestReturnErrorCodeOnException(): void
    {
        $this->registerFakeHandler(static fn(RequestInterface $request) => Create::rejectionFor(new ConnectException('Timeout', $request)));

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error')->with(self::stringContains('Timeout'));

        $clientService = $this->getClientService();
        $clientService->setLogger($logger);

        self::assertSame(900, $clientService->runSingleRequest('https://example.com/page'));
    }

    public function testBuildClientEventIsDispatched(): void
    {
        $this->registerFakeHandler(static fn() => Create::promiseFor(new Response(200)));

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(static fn(BuildClientEvent $event): bool => false === $event->getOptions()['allow_redirects']))
            ->willReturnArgument(0);

        $clientService = new ClientService($eventDispatcher);
        $clientService->setLogger(new NullLogger());

        self::assertSame(200, $clientService->runSingleRequest('https://example.com/'));
    }

    /**
     * Register a middleware via the TYPO3 HTTP configuration, that answers all requests without network access.
     */
    protected function registerFakeHandler(callable $responseFactory): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['HTTP'] = [
            'verify' => 'true',
            'handler' => [
                fn(callable $handler): callable => function (RequestInterface $request, array $options) use ($responseFactory) {
                    $this->requests[] = $request;

                    return $responseFactory($request);
                },
            ],
        ];
    }

    protected function getClientService(): ClientService
    {
        $eventDispatcher = $this->createStub(EventDispatcherInterface::class);
        $eventDispatcher->method('dispatch')->willReturnArgument(0);

        $clientService = new ClientService($eventDispatcher);
        $clientService->setLogger(new NullLogger());

        return $clientService;
    }
}

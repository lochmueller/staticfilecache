<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Cache;

use Psr\EventDispatcher\EventDispatcherInterface;
use SFC\Staticfilecache\Cache\IdentifierBuilder;
use SFC\Staticfilecache\Cache\StaticFileBackend;
use SFC\Staticfilecache\Cache\UriFrontend;
use SFC\Staticfilecache\Event\GeneratorCreate;
use SFC\Staticfilecache\Event\GeneratorRemove;
use SFC\Staticfilecache\Service\QueueService;
use SFC\Staticfilecache\Tests\Unit\AbstractTest;
use SFC\Staticfilecache\Tests\Unit\Fixtures\RecordingEventDispatcher;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Page\PageInformation;

/**
 * StaticFileBackendTest.
 *
 * @internal
 * @coversNothing
 */
final class StaticFileBackendTest extends AbstractTest
{
    private const URI = 'https://www.example.com/page/';

    private Connection $connection;

    private string $cacheDirectory;

    private string $relativeCacheDirectory;

    private RecordingEventDispatcher $eventDispatcher;

    private UriFrontend $frontend;

    protected function setUp(): void
    {
        parent::setUp();
        $GLOBALS['EXEC_TIME'] = time();
        $GLOBALS['TYPO3_REQUEST'] = new ServerRequest(self::URI);
        $this->connection = $this->createDatabase(
            'CREATE TABLE cache_staticfilecache (id INTEGER PRIMARY KEY, identifier TEXT, expires INTEGER, content BLOB)',
            'CREATE TABLE cache_staticfilecache_tags (id INTEGER PRIMARY KEY, identifier TEXT, tag TEXT)',
            'CREATE TABLE tx_staticfilecache_queue (
                uid INTEGER PRIMARY KEY,
                cache_url TEXT NOT NULL,
                cache_priority INTEGER DEFAULT 0 NOT NULL,
                page_uid INTEGER DEFAULT 0 NOT NULL,
                invalid_date INTEGER DEFAULT 0 NOT NULL,
                call_date INTEGER DEFAULT 0 NOT NULL,
                call_result TEXT NOT NULL
            )',
        );
        $this->relativeCacheDirectory = 'typo3temp/var/tests/sfc_' . bin2hex(random_bytes(8)) . '/';
        $this->cacheDirectory = Environment::getPublicPath() . '/' . $this->relativeCacheDirectory;
        GeneralUtility::mkdir_deep($this->cacheDirectory);
        $this->testFilesToDelete[] = $this->cacheDirectory;

        $this->eventDispatcher = new RecordingEventDispatcher();
        $this->registerContainerServices([
            EventDispatcherInterface::class => $this->eventDispatcher,
            IdentifierBuilder::class => new IdentifierBuilder($this->eventDispatcher),
        ]);
    }

    public function testSetStoreEntryAndDispatchGeneratorEvents(): void
    {
        $this->configure();
        $backend = $this->getBackend();
        $response = new Response();

        $this->frontend->set(self::URI, $response, ['pageId_1'], 3600);

        $createEvents = $this->eventDispatcher->getEvents(GeneratorCreate::class);
        self::assertCount(1, $createEvents);
        self::assertSame(self::URI, $createEvents[0]->getEntryIdentifier());
        self::assertSame($this->cacheDirectory . 'https_www.example.com_443/page/index', $createEvents[0]->getFileName());
        self::assertSame($response, $createEvents[0]->getResponse());
        self::assertSame(3600, $createEvents[0]->getLifetime());
        self::assertCount(1, $this->eventDispatcher->getEvents(GeneratorRemove::class));
        self::assertDirectoryExists($this->cacheDirectory . 'https_www.example.com_443/page');

        self::assertTrue($backend->has(self::URI));
        $data = $backend->get(self::URI);
        self::assertSame(3600, $data['expires'] - $data['created']);
        self::assertSame(0, $data['priority']);
        self::assertSame([self::URI], array_values($backend->findIdentifiersByTag('pageId_1')));
    }

    public function testSetExplanationOnlyStoresDetails(): void
    {
        $this->configure();
        $backend = $this->getBackend();
        $response = (new Response())->withHeader('X-SFC-Explanation', ['Not cacheable']);

        $this->frontend->set(self::URI, $response, ['explanation', 'pageId_1'], 3600);

        self::assertSame([], $this->eventDispatcher->getEvents(GeneratorCreate::class));
        self::assertSame([], $this->eventDispatcher->getEvents(GeneratorRemove::class));
        self::assertSame(['Not cacheable'], $backend->get(self::URI)['explanation']);
    }

    public function testSetWithHashedIdentifier(): void
    {
        $this->configure(['hashUriInCache' => '1']);

        $this->getBackend();
        $this->frontend->set(self::URI, new Response(), [], 3600);

        $row = $this->connection->select(['identifier', 'content'], 'cache_staticfilecache')->fetchAssociative();
        self::assertSame(hash('sha256', self::URI), $row['identifier']);
        self::assertSame(self::URI, unserialize($row['content'])['url']);
    }

    public function testGetPriorityFromUriLengthAndPageRecord(): void
    {
        $this->configure(['useReverseUriLengthInPriority' => '1']);
        $pageInformation = new PageInformation();
        $pageInformation->setPageRecord(['tx_staticfilecache_cache_priority' => 5]);
        $GLOBALS['TYPO3_REQUEST'] = (new ServerRequest(self::URI))->withAttribute('frontend.page.information', $pageInformation);
        $backend = $this->getBackend();

        $this->frontend->set(self::URI, new Response(), [], 3600);

        self::assertSame(QueueService::PRIORITY_MEDIUM - \strlen(self::URI) + 5, $backend->get(self::URI)['priority']);
    }

    public function testGetAndRemoveUnknownEntry(): void
    {
        $this->configure();
        $backend = $this->getBackend();

        self::assertFalse($backend->has(self::URI));
        self::assertFalse($backend->get(self::URI));
        self::assertFalse($backend->remove(self::URI));
    }

    public function testRemoveExistingEntry(): void
    {
        $this->configure();
        $backend = $this->getBackend();
        $this->frontend->set(self::URI, new Response(), [], 3600);
        $this->eventDispatcher->events = [];

        self::assertTrue($backend->remove(self::URI));

        self::assertFalse($backend->has(self::URI));
        self::assertCount(1, $this->eventDispatcher->getEvents(GeneratorRemove::class));
    }

    public function testFlushByTagRemoveStaticFiles(): void
    {
        $this->configure();
        $backend = $this->getBackend();
        $this->frontend->set(self::URI, new Response(), ['pageId_1'], 3600);
        $this->frontend->set('https://www.example.com/other/', new Response(), ['pageId_2'], 3600);
        $this->eventDispatcher->events = [];

        $backend->flushByTag('pageId_1');

        self::assertFalse($backend->has(self::URI));
        self::assertTrue($backend->has('https://www.example.com/other/'));
        $removeEvents = $this->eventDispatcher->getEvents(GeneratorRemove::class);
        self::assertCount(1, $removeEvents);
        self::assertSame(self::URI, $removeEvents[0]->getEntryIdentifier());
    }

    public function testFlushByTagsRemoveStaticFiles(): void
    {
        $this->configure();
        $backend = $this->getBackend();
        $this->frontend->set(self::URI, new Response(), ['pageId_1'], 3600);
        $this->frontend->set('https://www.example.com/other/', new Response(), ['pageId_2'], 3600);

        $backend->flushByTags([]);
        self::assertTrue($backend->has(self::URI));

        $backend->flushByTags(['pageId_1', 'pageId_2']);
        self::assertFalse($backend->has(self::URI));
        self::assertFalse($backend->has('https://www.example.com/other/'));
    }

    public function testFlushByTagInBoostModeAddEntriesToQueue(): void
    {
        $this->configure(['boostMode' => '1']);
        $backend = $this->getBackend();
        $this->frontend->set(self::URI, new Response(), ['pageId_1'], 3600);
        $this->eventDispatcher->events = [];

        $backend->flushByTag('pageId_1');

        self::assertTrue($backend->has(self::URI));
        self::assertSame([], $this->eventDispatcher->getEvents(GeneratorRemove::class));
        self::assertSame(self::URI, $this->connection->select(['cache_url'], 'tx_staticfilecache_queue')->fetchOne());
    }

    public function testBoostModeCouldBeIgnoredViaEnvironment(): void
    {
        $this->configure(['boostMode' => '1']);
        $backend = $this->getBackend();
        $this->frontend->set(self::URI, new Response(), ['pageId_1'], 3600);

        putenv('STATIC_FILE_CACHE_IGNORE_BOOST_QUEUE=1');
        try {
            $backend->flushByTag('pageId_1');
        } finally {
            putenv('STATIC_FILE_CACHE_IGNORE_BOOST_QUEUE');
        }

        self::assertFalse($backend->has(self::URI));
        self::assertSame(0, (int) $this->connection->count('*', 'tx_staticfilecache_queue', []));
    }

    public function testFlushOnlyCurrentDomainByDefault(): void
    {
        $this->configure(['clearCacheForAllDomains' => '0']);
        $_SERVER['HTTP_HOST'] = 'www.example.com';
        $backend = $this->getBackend();
        $this->frontend->set(self::URI, new Response(), ['sfc_domain_www_example_com'], 3600);
        $this->frontend->set('https://www.other.com/', new Response(), ['sfc_domain_www_other_com'], 3600);

        $backend->flush();

        self::assertFalse($backend->has(self::URI));
        self::assertTrue($backend->has('https://www.other.com/'));
    }

    public function testFlushAllDomains(): void
    {
        $this->configure(['clearCacheForAllDomains' => '1']);
        $backend = $this->getBackend();
        $this->frontend->set(self::URI, new Response(), [], 3600);
        GeneralUtility::writeFile($this->cacheDirectory . 'https_www.example.com_443/page/index.html', 'content');

        $backend->flush();

        self::assertFalse($backend->has(self::URI));
        self::assertSame(0, (int) $this->connection->count('*', 'cache_staticfilecache', []));
        self::assertDirectoryDoesNotExist($this->cacheDirectory . 'https_www.example.com_443');
    }

    public function testFlushInBoostModeRefillQueue(): void
    {
        $this->configure(['clearCacheForAllDomains' => '1', 'boostMode' => '1']);
        $backend = $this->getBackend();
        $this->frontend->set(self::URI, new Response(), [], 3600);
        $this->frontend->set('https://www.example.com/other/', new Response(), [], 3600);

        $backend->flush();

        self::assertTrue($backend->has(self::URI));
        $queued = $this->connection->select(['cache_url'], 'tx_staticfilecache_queue')->fetchFirstColumn();
        sort($queued);
        self::assertSame(['https://www.example.com/other/', self::URI], $queued);
    }

    public function testCollectGarbageRemoveExpiredEntries(): void
    {
        $this->configure();
        $backend = $this->getBackend();
        $this->frontend->set(self::URI, new Response(), [], 3600);
        $this->connection->update('cache_staticfilecache', ['expires' => 1], ['identifier' => self::URI]);
        $this->eventDispatcher->events = [];

        $backend->collectGarbage();

        self::assertSame(0, (int) $this->connection->count('*', 'cache_staticfilecache', []));
        $removeEvents = $this->eventDispatcher->getEvents(GeneratorRemove::class);
        self::assertCount(1, $removeEvents);
        self::assertSame(self::URI, $removeEvents[0]->getEntryIdentifier());
    }

    private function configure(array $configuration = []): void
    {
        $this->setExtensionConfiguration(array_merge(['overrideCacheDirectory' => $this->relativeCacheDirectory], $configuration));
    }

    private function getBackend(): StaticFileBackend
    {
        $backend = new StaticFileBackend(['defaultLifetime' => 3600]);
        // The frontend passes the response object directly to the transient backend (like in the frontend rendering)
        $this->frontend = new UriFrontend('staticfilecache', $backend);

        return $backend;
    }
}

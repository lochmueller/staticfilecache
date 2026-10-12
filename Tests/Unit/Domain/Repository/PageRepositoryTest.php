<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Domain\Repository;

use SFC\Staticfilecache\Domain\Repository\PageRepository;
use SFC\Staticfilecache\Tests\Unit\AbstractTest;

/**
 * PageRepositoryTest.
 *
 * @internal
 * @coversNothing
 */
final class PageRepositoryTest extends AbstractTest
{
    protected function setUp(): void
    {
        parent::setUp();
        $connection = $this->createDatabase('CREATE TABLE pages (uid INTEGER PRIMARY KEY, pid INTEGER, title TEXT)');
        $connection->insert('pages', ['uid' => 1, 'pid' => 0, 'title' => 'Root']);
        $connection->insert('pages', ['uid' => 2, 'pid' => 1, 'title' => 'Child 1']);
        $connection->insert('pages', ['uid' => 3, 'pid' => 1, 'title' => 'Child 2']);
        $connection->insert('pages', ['uid' => 4, 'pid' => 2, 'title' => 'Sub child']);
    }

    /**
     * @return iterable<string, array{string, list<int>}>
     */
    public static function displayModeProvider(): iterable
    {
        yield 'current' => ['current', [1]];
        yield 'childs' => ['childs', [2, 3]];
        yield 'both' => ['both', [1, 2, 3]];
        yield 'fallback' => ['invalid', [1, 2, 3]];
    }

    /**
     * @param list<int> $expectedUids
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('displayModeProvider')]
    public function testFindForBackend(string $displayMode, array $expectedUids): void
    {
        $rows = iterator_to_array((new PageRepository())->findForBackend(1, $displayMode), false);
        $uids = array_map('intval', array_column($rows, 'uid'));
        sort($uids);

        self::assertSame($expectedUids, $uids);
    }
}

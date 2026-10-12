<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Fixtures;

use SFC\Staticfilecache\Controller\BackendController;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;

/**
 * BackendController that exposes internals for tests.
 *
 * @internal
 */
class TestBackendController extends BackendController
{
    public function setTestRequest(RequestInterface $request): void
    {
        $this->request = $request;
    }

    public function callSetFilter(string $filter): string
    {
        return $this->setFilter($filter);
    }

    public function callGetDisplayMode(): string
    {
        return $this->getDisplayMode();
    }

    public function callGetCurrentUid(): int
    {
        return $this->getCurrentUid();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function callGetCachePagesEntries(string $filter): array
    {
        return $this->getCachePagesEntries($filter);
    }
}

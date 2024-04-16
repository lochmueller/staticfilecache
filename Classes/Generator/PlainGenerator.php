<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Generator;

use Psr\Http\Message\ResponseInterface;
use SFC\Staticfilecache\Service\RemoveService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PlainGenerator extends AbstractGenerator
{
    public function generate(string $entryIdentifier, string $fileName, ResponseInterface $response, int $lifetime): void
    {
        $this->writeFile($fileName, (string) $response->getBody());
    }

    public function remove(string $entryIdentifier, string $fileName): void
    {
        $this->removeFile($fileName);
    }
}

<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Service\InlineAssets;

use SFC\Staticfilecache\Service\InlineAssets\AbstractInlineAssets;
use SFC\Staticfilecache\Tests\Unit\AbstractTest;

/**
 * AbstractInlineAssetsTest.
 */
abstract class AbstractInlineAssetsTest extends AbstractTest
{
    protected string $sitePath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sitePath = rtrim($this->createTemporaryDirectory(), '/');
    }

    /**
     * Create a file relative to the (temporary) site path.
     */
    protected function createFile(string $path, string $content): void
    {
        $absolutePath = $this->sitePath . $path;
        if (!is_dir(\dirname($absolutePath))) {
            mkdir(\dirname($absolutePath), 0o777, true);
        }
        file_put_contents($absolutePath, $content);
    }

    /**
     * @template T of AbstractInlineAssets
     *
     * @param class-string<T> $className
     *
     * @return T
     */
    protected function getService(string $className, array $configuration = []): AbstractInlineAssets
    {
        $this->setExtensionConfiguration(array_merge(['inlineAssetsFileSize' => '10000'], $configuration));
        $service = new $className();
        (new \ReflectionProperty($service, 'sitePath'))->setValue($service, $this->sitePath);

        return $service;
    }
}

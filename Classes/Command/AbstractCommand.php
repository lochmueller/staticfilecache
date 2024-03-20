<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Command;

use Symfony\Component\Console\Command\Command;

abstract class AbstractCommand extends Command
{
    public function setDescription(string $description): static
    {
        return parent::setDescription('StaticFileCache task: ' . $description);
    }
}

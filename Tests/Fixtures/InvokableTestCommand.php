<?php

namespace Symfony\Component\Console\Tests\Fixtures;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;

#[AsCommand('invokable:test')]
class InvokableTestCommand
{
    public function __invoke(): int
    {
        return Command::SUCCESS;
    }
}
